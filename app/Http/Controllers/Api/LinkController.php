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
            $query = Links::query()
                ->search($request->query('q'))
                ->active($request->query('active'))
                ->expired($request->query('expired'))
                ->userId(auth()->id()); // Use authenticated user

            // Add sorting
            $sort = $request->query('sort', '-created_at');
            if ($sort) {
                $direction = 'asc';
                $field = $sort;

                if (str_starts_with($sort, '-')) {
                    $direction = 'desc';
                    $field = substr($sort, 1);
                }

                if (in_array($field, ['created_at', 'clicks_count', 'slug'])) {
                    $query->orderBy($field, $direction);
                }
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
        // $this->authorize('create', Links::class);

        try {
            $validated = $my->validated();

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = $this->generateUniqueSlug();
            }

            $link = Links::create($validated + ['user_id' => auth()->id()]); // Use authenticated user
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

    private function generateUniqueSlug($length = 6)
    {
        do {
            $slug = strtolower(bin2hex(random_bytes($length / 2)));
        } while (Links::where('slug', $slug)->exists());

        return $slug;
    }
}
