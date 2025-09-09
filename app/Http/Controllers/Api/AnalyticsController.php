<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get statistics for a specific link.
     */
    public function linkStats(Request $request, $id)
    {
        try {
            $link = Link::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }

            $totalClicks = Click::where('link_id', $id)->count();
            $clicksByDay = Click::where('link_id', $id)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw(value: 'count(*) as clicks'))
                ->groupBy('date')
                ->get();

            $topReferrers = Click::where('link_id', $id)
                ->select('referrer', DB::raw('count(*) as clicks'))
                ->groupBy('referrer')
                ->orderByDesc('clicks')
                ->take(5)
                ->get();

            $topCountries = Click::where('link_id', $id)
                ->select('country', DB::raw('count(*) as clicks'))
                ->groupBy('country')
                ->orderByDesc('clicks')
                ->take(5)
                ->get();

            return response()->json([
                'data' => [
                    'total_clicks' => $totalClicks,
                    'clicks_by_day' => $clicksByDay,
                    'top_referrers' => $topReferrers,
                    'top_countries' => $topCountries,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve link stats', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get overview analytics for all links of the authenticated user.
     */
    public function overview(Request $request)
    {
        try {
            $userId = auth()->id();

            $totalClicks = Click::whereHas('link', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count();

            $clicksByDay = Click::whereHas('link', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as clicks'))
                ->groupBy('date')
                ->get();

            return response()->json([
                'data' => [
                    'total_clicks' => $totalClicks,
                    'clicks_by_day' => $clicksByDay,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve analytics overview', 'message' => $e->getMessage()], 500);
        }
    }
}
