<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Links;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function linkStats(Request $request, $id)
    {
        try {
            $link = Links::findOrFail($id);

            // TODO: Add authorization check
            // $this->authorize('view', $link);

            $range = $request->query('range', '7d');
            $days = $this->parseDaysFromRange($range);
            $startDate = Carbon::now()->subDays($days);

            // Get total clicks in the range
            $totalClicks = Click::where('link_id', $link->id)
                ->where('occurred_at', '>=', $startDate)
                ->count();

            // Get timeseries data (clicks per day)
            $timeseries = Click::where('link_id', $link->id)
                ->where('occurred_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(occurred_at) as date'),
                    DB::raw('COUNT(*) as clicks')
                )
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->orderBy('date')
                ->get()
                ->pluck('clicks', 'date')
                ->toArray();

            // Get top referrers
            $topReferrers = Click::where('link_id', $link->id)
                ->where('occurred_at', '>=', $startDate)
                ->whereNotNull('referrer')
                ->select('referrer', DB::raw('COUNT(*) as count'))
                ->groupBy('referrer')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'referrer')
                ->toArray();

            // Get top countries
            $topCountries = Click::where('link_id', $link->id)
                ->where('occurred_at', '>=', $startDate)
                ->whereNotNull('country')
                ->select('country', DB::raw('COUNT(*) as count'))
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'country')
                ->toArray();

            return response()->json([
                'data' => [
                    'link_id' => $link->id,
                    'slug' => $link->slug,
                    'range' => $range,
                    'total_clicks' => $totalClicks,
                    'timeseries' => $timeseries,
                    'top_referrers' => $topReferrers,
                    'top_countries' => $topCountries,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get link stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function overview(Request $request)
    {
        try {
            $userId = auth()->id(); // Use authenticated user
            $range = $request->query('range', '30d');
            $days = $this->parseDaysFromRange($range);
            $startDate = Carbon::now()->subDays($days);

            // Get user's links
            $linkIds = Links::where('user_id', $userId)->pluck('id');

            // Total clicks across all user's links
            $totalClicks = Click::whereIn('link_id', $linkIds)
                ->where('occurred_at', '>=', $startDate)
                ->count();

            // Total links
            $totalLinks = $linkIds->count();

            // Active links
            $activeLinks = Links::where('user_id', $userId)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->count();

            // Top performing links
            $topLinks = Click::whereIn('link_id', $linkIds)
                ->where('occurred_at', '>=', $startDate)
                ->select('link_id', DB::raw('COUNT(*) as clicks'))
                ->groupBy('link_id')
                ->orderByDesc('clicks')
                ->limit(10)
                ->with('link:id,slug,target_url')
                ->get()
                ->map(function ($item) {
                    return [
                        'link_id' => $item->link_id,
                        'slug' => $item->link->slug,
                        'target_url' => $item->link->target_url,
                        'clicks' => $item->clicks,
                    ];
                });

            // Daily timeseries for all links
            $timeseries = Click::whereIn('link_id', $linkIds)
                ->where('occurred_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(occurred_at) as date'),
                    DB::raw('COUNT(*) as clicks')
                )
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->orderBy('date')
                ->get()
                ->pluck('clicks', 'date')
                ->toArray();

            return response()->json([
                'data' => [
                    'user_id' => $userId,
                    'range' => $range,
                    'total_clicks' => $totalClicks,
                    'total_links' => $totalLinks,
                    'active_links' => $activeLinks,
                    'top_links' => $topLinks,
                    'timeseries' => $timeseries,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get overview analytics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function parseDaysFromRange($range)
    {
        // Parse ranges like "7d", "30d", "90d"
        if (preg_match('/^(\d+)d$/', $range, $matches)) {
            return (int) $matches[1];
        }

        return 7; // Default to 7 days
    }
}
