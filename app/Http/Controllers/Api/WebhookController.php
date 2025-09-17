<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Jobs\Webhook as WebhookJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Retry sending a webhook
     */
    public function retry(Request $request, $id)
    {
        try {
            $webhook = Webhook::find($id);
            if (!$webhook) {
                return response()->json(['error' => 'Webhook not found'], 404);
            }

            // Check if user owns the link or is admin
            if (!$this->canAccessWebhook($webhook)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Reset webhook status and dispatch job
            $webhook->update([
                'status' => 'pending',
                'last_error' => null
            ]);

            // Dispatch webhook job
            WebhookJob::dispatch($webhook->link, $webhook->payload['data']['threshold'] ?? 0, $webhook->payload['data']['clicks_count'] ?? 0);

            return response()->json([
                'message' => 'Webhook retry initiated',
                'webhook_id' => $webhook->id
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retry webhook', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * List webhooks for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $query = Webhook::with('link');
            
            // Admin can see all webhooks, regular users see only their own
            if (!$this->isAdmin()) {
                $query->whereHas('link', function($q) {
                    $q->where('user_id', auth()->id());
                });
            }

            $webhooks = $query->paginate(min(max($request->query('per_page', 10), 1), 100))
                ->appends($request->query());

            return response()->json($webhooks, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve webhooks', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if user is admin
     */
    private function isAdmin(): bool
    {
        return request()->header('X-Is-Admin') == '1' || env('IS_ADMIN') == true;
    }

    /**
     * Check if user can access the webhook (owner or admin)
     */
    private function canAccessWebhook(Webhook $webhook): bool
    {
        return $this->isAdmin() || $webhook->link->user_id == auth()->id();
    }
}
