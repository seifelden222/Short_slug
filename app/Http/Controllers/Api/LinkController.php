<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LinksRequest;
use App\Http\Resources\LinksResource;
use App\Models\Link;

use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Show user's own links or all links if admin
            $query = Link::query()
                ->search($request->query('q'))
                ->active($request->query('is_active'))
                ->expired($request->query('is_expired'))
                ->sort($request->query('sort'));
            
            // Admin can see all links, regular users see only their own
            if (!$this->isAdmin()) {
                $query->userId(auth()->id());
            }
            
            $links = $query->paginate(min(max($request->query('per_page', 10), 1), 100))
                ->appends($request->query());
            return LinksResource::collection($links);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve links', 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(LinksRequest $my)
    {
        try {
            $validated = $my->validated();
            $validated['user_id'] = auth()->id(); // Fix: Set current user ID
            $link = Link::create($validated); 
            return new LinksResource($link);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create link', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $link = Link::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }
            
            // Check if user owns the link or is admin
            if (!$this->canAccessLink($link)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            
            return new LinksResource($link);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve link', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, LinksRequest $my)
    {
        try {
            $link = Link::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }
            
            // Check if user owns the link or is admin
            if (!$this->canAccessLink($link)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            
            $validated = $my->validated();
            $link->update($validated);
            return new LinksResource($link);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update link', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $link = Link::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }
            
            // Check if user owns the link or is admin
            if (!$this->canAccessLink($link)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            
            $link->delete();
            return response()->json(['message' => 'Link deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete link', 'message' => $e->getMessage()], 500);
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
     * Check if user can access the link (owner or admin)
     */
    private function canAccessLink(Link $link): bool
    {
        return $this->isAdmin() || $link->user_id == auth()->id();
    }
}
