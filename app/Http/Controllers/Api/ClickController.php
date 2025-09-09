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
            $link = Link::query()->where(
                'slug',
                $slug
            )->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->first();
            if (!$link) {
                return response()->json(['error' => 'Link not found or expired'], 404);
            }
            // Check idempotency
            $idempotency = $request->input('idempotency_key');
            if ($idempotency) {
                $existingClick = Click::where('link_id', $link->id)
                    ->where('idempotency_key', $idempotency)->first();
                if ($existingClick) {
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
                }
            }
            RateLimiter::hit($key, 60);
            DB::transaction(function () use ($link, $request, $idempotency) {
                // Check for existing record with idempotency_key
                $existingClick = Click::where('link_id', $link->id)
                    ->where('idempotency_key', $idempotency)
                    ->first();

                if ($existingClick) {
                    // If record exists, return the same result
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
                }

                // If not exists, create the record
                Click::create([
                    'link_id' => $link->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $request->input('referrer'),
                    'country' => null,
                    'idempotency_key' => $idempotency
                ]);
                // Increment click count
                $link->increment('clicks_count');
            });
            // $link->updated('clicks_count');
            $link->refresh();
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
            return response()->json(['error' => 'Failed to process click', 'message' => $e->getMessage()], 500);
        }
    }
}
