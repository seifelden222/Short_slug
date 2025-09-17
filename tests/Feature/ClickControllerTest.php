<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Link;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Webhook;

class ClickControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $link;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->link = Link::factory()->create([
            'user_id' => $this->user->id,
            'slug' => 'test-link',
            'is_active' => true,
        ]);
    }

    public function test_click_records_successfully()
    {
        $response = $this->postJson("/api/r/{$this->link->slug}", [
            'idempotency_key' => 'test-click-1',
            'referrer' => 'https://google.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'slug' => $this->link->slug,
            'accepted' => true,
            'clicks_count' => 1,
        ]);

        $this->assertDatabaseHas('clicks', [
            'link_id' => $this->link->id,
            'idempotency_key' => 'test-click-1',
        ]);
    }

    public function test_idempotency_prevents_duplicate_clicks()
    {
        $clickData = [
            'idempotency_key' => 'test-click-duplicate',
            'referrer' => 'https://google.com',
        ];

        // First click
        $response1 = $this->postJson("/api/r/{$this->link->slug}", $clickData);
        $response1->assertStatus(200);

        // Second click with same idempotency key
        $response2 = $this->postJson("/api/r/{$this->link->slug}", $clickData);
        $response2->assertStatus(200);
        $response2->assertJsonFragment(['idempotent' => true]);

        // Should only have one click record
        $this->assertEquals(1, Click::where('link_id', $this->link->id)->count());
    }

    public function test_rate_limiting_works()
    {
        // Simulate multiple rapid clicks from same IP
        for ($i = 0; $i < 4; $i++) {
            $response = $this->postJson("/api/r/{$this->link->slug}", [
                'idempotency_key' => "click-{$i}",
            ]);
            
            if ($i < 3) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Rate limited
            }
        }
    }

    public function test_inactive_link_returns_404()
    {
        $this->link->update(['is_active' => false]);

        $response = $this->postJson("/api/r/{$this->link->slug}");

        $response->assertStatus(404);
    }

    public function test_expired_link_returns_404()
    {
        $this->link->update(['expires_at' => now()->subDay()]);

        $response = $this->postJson("/api/r/{$this->link->slug}");

        $response->assertStatus(404);
    }

    public function test_webhook_job_dispatched_on_threshold()
    {
        Queue::fake();

        // Set clicks_count close to threshold
        $this->link->update(['clicks_count' => 9]);

        $response = $this->postJson("/api/r/{$this->link->slug}", [
            'idempotency_key' => 'threshold-test',
        ]);

        $response->assertStatus(200);
        
        // Should dispatch webhook job when crossing threshold 10
        Queue::assertPushed(Webhook::class);
    }
}
