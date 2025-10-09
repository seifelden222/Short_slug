<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class ClickController extends Controller
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
            $link = Link::query()
                ->where('slug', $slug)
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
            $idempotency = $request->input('idempotency_key');

            // First check if the click with the same idempotency_key exists
            $existingClick = null;
            if ($idempotency) {
                $existingClick = Click::where('link_id', $link->id)
                    ->where('idempotency_key', $idempotency)
                    ->first();
            }

            if ($existingClick) {
                // If the click already exists, return the same result as the original click
                return response()->json([
                    'data' => [
                        'link_id' => $link->id,
                        'slug' => $link->slug,
                        'target_url' => $link->target_url,
                        'accepted' => true,
                        'ip' => $request->ip(),
                        'clicks_count' => $link->clicks_count,
                        'idempotent' => true,
                    ]
                ], 200);
            }

            // Apply rate limiting
            RateLimiter::hit($key, 60);


            // Start DB transaction to save the new click. Collect any thresholds crossed and dispatch after commit.
            $jobsToDispatch = [];
            DB::transaction(function () use ($link, $request, $idempotency, &$jobsToDispatch) {
                // Record the new click
                $oldcount = $link->clicks_count ?? 0;
                Click::create([
                    'link_id' => $link->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $request->input('referrer'),
                    'country' => null,
                    'idempotency_key' => $idempotency
                ]);

                // Increment the click count for the link
                $link->increment('clicks_count');
                $link->refresh();
                $newcount = $link->clicks_count;

                // If crossing thresholds, queue local job descriptors to dispatch after commit
                $thresholds = [10, 100, 1000];
                foreach ($thresholds as $t) {
                    if ($oldcount < $t && $newcount >= $t) {
                        $jobsToDispatch[] = ['link' => $link, 'threshold' => $t, 'clicks' => $newcount];
                    }
                }
            });

            // Dispatch webhook jobs after transaction commits
            foreach ($jobsToDispatch as $j) {
                \App\Jobs\Webhook::dispatch($j['link'], $j['threshold'], $j['clicks']);
            }

            $link->refresh();
            return response()->json([
                'data' => [
                    'link_id' => $link->id,
                    'slug' => $link->slug,
                    'target_url' => $link->target_url,
                    'accepted' => true,
                    'ip' => $request->ip(),
                    'clicks_count' => $link->clicks_count
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process click', 'message' => $e->getMessage()], 500);
        }
    }
}

