<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Links;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class RedirectController extends Controller
{
    public function redirect(Request $request, string $slug)
    {
        try {
            // Rate limiting per (IP, slug) - max 3 per minute
            $key = 'redirect:' . $request->ip() . ':' . $slug;

            if (RateLimiter::tooManyAttempts($key, 3)) {
                return response()->json([
                    'error' => 'Too many clicks. Please try again later.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            // Find active, non-expired link
            $link = Links::where('slug', $slug)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();

            if (!$link) {
                return response()->json(['error' => 'Link not found or expired'], 404);
            }

            // Check idempotency
            $idempotencyKey = $request->input('idempotency_key');

            if ($idempotencyKey) {
                $existingClick = Click::where('link_id', $link->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingClick) {
                    // Return original success response
                    return response()->json([
                        'data' => [
                            'link_id' => $link->id,
                            'slug' => $link->slug,
                            'target_url' => $link->target_url,
                            'accepted' => true,
                            'clicks_count' => $link->clicks_count
                        ]
                    ], 200);
                }
            }

            // Record the rate limit attempt
            RateLimiter::hit($key, 60); // 1 minute decay

            // Transaction: create click + increment counter
            DB::transaction(function () use ($link, $request, $idempotencyKey) {
                // Create click record
                Click::create([
                    'link_id' => $link->id,
                    'occurred_at' => now(),
                    'ip' => $request->ip(),
                    'ua' => $request->userAgent(),
                    'referrer' => $request->input('referrer'),
                    'country' => null, // Could integrate with GeoIP service
                    'idempotency_key' => $idempotencyKey,
                ]);

                // Increment clicks_count atomically
                $link->increment('clicks_count');
            });

            // Refresh the model to get updated clicks_count
            $link->refresh();

            // Check for webhook thresholds (10, 100, 1000)
            $this->checkWebhookThresholds($link);

            return response()->json([
                'data' => [
                    'link_id' => $link->id,
                    'slug' => $link->slug,
                    'target_url' => $link->target_url,
                    'accepted' => true,
                    'clicks_count' => $link->clicks_count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Redirect failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function checkWebhookThresholds(Links $link)
    {
        $thresholds = [10, 100, 1000];
        $currentCount = $link->clicks_count;
        $previousCount = $currentCount - 1;

        foreach ($thresholds as $threshold) {
            if ($currentCount >= $threshold && $previousCount < $threshold) {
                // Threshold crossed, enqueue webhook event
                // For now, just log it (you can implement proper webhook queue later)
                Log::info("Webhook threshold reached", [
                    'link_id' => $link->id,
                    'threshold' => $threshold,
                    'clicks_count' => $currentCount
                ]);

                // TODO: Implement webhook dispatch
                // dispatch(new ThresholdReachedJob($link, $threshold));
            }
        }
    }
}
