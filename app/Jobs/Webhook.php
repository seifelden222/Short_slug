<?php

namespace App\Jobs;

use App\Models\Link;
use App\Models\Webhook as WebhookModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Webhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Link $link;
    public int $threshold;
    public int $clicks;

    /**
     * Create a new job instance.
     */
    public function __construct(Link $link, int $threshold, int $clicks = 0)
    {
        $this->link = $link;
        $this->threshold = $threshold;
        $this->clicks = $clicks;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // The target URL for webhooks can be configured via env WEBHOOK_TARGET_URL
        // fall back to the env var when a per-link webhook target isn't present
        $target = $this->link->target_url ?? env('WEBHOOK_TARGET_URL');

        // Build payload and compute optional HMAC signature using WEBHOOK_SECRET
        $payload = [
            'data' => [
                'link_id' => $this->link->id,
                'slug' => $this->link->slug,
                'target_url' => $this->link->target_url,
                'clicks_count' => $this->clicks,
                'threshold' => $this->threshold,
            ]
        ];

    $body = json_encode($payload);
    $secret = env('WEBHOOK_SECRET');
    $signature = $secret ? hash_hmac('sha256', $body, $secret) : null;
        // Create DB record for the webhook attempt
        $webhook = WebhookModel::create([
            'link_id' => $this->link->id,
            'payload' => $payload,
            'event' => 'link.threshold.reached',
            'target_url' => $target ?? 'not-configured',
            'status' => $target ? 'pending' : 'failed',
            'attempts' => 0,
        ]);

        if (!$target) {
            Log::warning('Webhook target not configured (WEBHOOK_TARGET_URL). Webhook recorded as failed.', ['link_id' => $this->link->id]);
            return;
        }

        try {
            $http = Http::timeout(5);
            if ($signature) {
                $http = $http->withHeaders([
                    'X-Signature' => $signature,
                    'Content-Type' => 'application/json'
                ]);
            }

            $response = $http->post($target, $payload);
       
            $webhook->attempts = $webhook->attempts + 1;
            if ($response->successful()) {
                $webhook->status = 'success';
                $webhook->last_error = null;
            } else {
                $webhook->status = 'failed';
                $webhook->last_error = 'HTTP ' . $response->status() . ' - ' . substr($response->body(), 0, 1000);
            }
            $webhook->save();
        } catch (\Exception $e) {
            $webhook->attempts = $webhook->attempts + 1;
            $webhook->status = 'failed';
            $webhook->last_error = $e->getMessage();
            $webhook->save();
            Log::error('Webhook delivery exception', ['link_id' => $this->link->id, 'error' => $e->getMessage()]);
            // rethrow to allow the queue worker to decide retries
            throw $e;
        }
    }
}
