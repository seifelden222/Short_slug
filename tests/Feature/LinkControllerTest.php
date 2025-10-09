<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Link;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_create_link()
    {
        $linkData = [
            'slug' => 'test-link',
            'target_url' => 'https://example.com',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/links', $linkData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('links', [
            'slug' => 'test-link',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_view_own_links()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/links');

        $response->assertStatus(200);
        $response->assertJsonFragment(['slug' => $link->slug]);
    }

    public function test_user_cannot_view_other_user_links()
    {
        $otherUser = User::factory()->create();
        $otherLink = Link::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/links');

        $response->assertStatus(200);
        $response->assertJsonMissing(['slug' => $otherLink->slug]);
    }

    public function test_user_can_update_own_link()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'target_url' => 'https://updated-example.com',
        ];

        $response = $this->putJson("/api/links/{$link->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'target_url' => 'https://updated-example.com',
        ]);
    }

    public function test_user_cannot_update_other_user_link()
    {
        $otherUser = User::factory()->create();
        $otherLink = Link::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'target_url' => 'https://malicious-update.com',
        ];

        $response = $this->putJson("/api/links/{$otherLink->id}", $updateData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('links', [
            'id' => $otherLink->id,
            'target_url' => 'https://malicious-update.com',
        ]);
    }

    public function test_validation_rules_work()
    {
        $invalidData = [
            'slug' => 'invalid slug with spaces',
            'target_url' => 'not-a-url',
        ];

        $response = $this->postJson('/api/links', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug', 'target_url']);
    }
}
