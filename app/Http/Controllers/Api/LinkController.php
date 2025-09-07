<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LinksRequest;
use App\Http\Resources\LinksResource;
use App\Models\Links;

use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // $this->authorize('viewAny', Links::class);
        try {
            $links = Links::query()
                ->search($request->query('q'))
                ->active($request->query('is_active'))
                ->expired($request->query('is_expired'))
                // ->sort($request->query('sort'))
                ->userId(auth()->id())
                ->paginate(min(max($request->query('per_page', 10), 1), 100))
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
        // $this->authorize('create', Links::class);

        try { 
            $validated = $my->validated();
            $link = Links::create($validated + ['user_id' => 1]); //->id() مؤقتاً لتجربة
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
            $link = Links::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }
            // $this->authorize('view', $link);
            return new LinksResource($link);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve link', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( string $id, LinksRequest $my)
    {
        try {
            $link = Links::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }
            // $this->authorize('update', $link);
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
            $link = Links::find($id);
            if (!$link) {
                return response()->json(['error' => 'Link not found'], 404);
            }
            // $this->authorize('delete', $link);
            $link->delete();
            return response()->json(['message' => 'Link deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete link', 'message' => $e->getMessage()], 500);
        }
    }
}
