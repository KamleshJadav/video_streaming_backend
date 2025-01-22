<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Actor;
use Illuminate\Support\Str;

class ActorController extends Controller
{
    // Add a new actor
    public function add(Request $request)
    {
        try {
            // Decode SEO tags if present
            $seoTags = json_decode($request->input('seo_teg'), true);

            // Validate incoming request
            $request->validate([
                'name' => 'required|string|max:50000',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp',
                'aliases' => 'required|string|max:50000',
                'seo_teg' => 'nullable|json',
                'birth_date' => 'nullable|string', // You can adjust this validation based on how birth_date is stored
                'place_of_birth' => 'nullable|string|max:50000',
                'description' => 'nullable|string',
                'like' => 'nullable|integer',
                'dislike' => 'nullable|integer',
                'ranking' => 'nullable|integer',
       
                'sorting_position' => 'nullable|integer',
            ]);

            // Handle the image upload if the image is provided
            if (strpos($request['image'], '.jpg') !== false || strpos($request['image'], '.png') !== false || strpos($request['image'], '.jpeg') !== false || strpos($request['image'], '.webp') !== false) {
                $photo = $request['image'];
            } else {
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $filename = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $photo = date('His') . '-' . Str::random(10) . '.' . $extension;
                    $file->move(public_path('image/actor'), $photo);
                } else {
                    $photo = "";
                }
            }

            // Create new actor record
            $actor = Actor::create([
                'name' => $request->name,
                'image' => $photo,
                'seo_teg' => $seoTags,
                'birth_date' => $request->birth_date,
                'place_of_birth' => $request->place_of_birth,
                'description' => $request->description,
                'like' => $request->like,
                'dislike' => $request->dislike,
                'ranking' => $request->ranking,
                'gender' => $request->gender,
                'total_image' => 0,
                'total_video' => 0,
                'sorting_position' => $request->sorting_position,
                'aliases' => $request->aliases,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Actor added successfully',
                'data' => $actor
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'seoTags' => $seoTags,
                'error' => 'Failed to add actor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing actor
    public function update(Request $request)
    {
        try {
            // Decode SEO tags if present
            $seoTags = json_decode($request->input('seo_teg'), true);

            // Validate incoming request
            $request->validate([
                'id' => 'required|integer|exists:actors,id',
                'name' => 'required|string|max:50000',
                'aliases' => 'required|string|max:50000',
                'seo_teg' => 'nullable|json',
                'birth_date' => 'nullable|string', // You can adjust this validation based on how birth_date is stored
                'place_of_birth' => 'nullable|string|max:50000',
                'description' => 'nullable|string',
                'like' => 'nullable|integer',
                'dislike' => 'nullable|integer',
                'ranking' => 'nullable|integer',
                
                'sorting_position' => 'nullable|integer',
            ]);

            // Handle the image upload if the image is provided
            if (strpos($request['image'], '.jpg') !== false || strpos($request['image'], '.png') !== false || strpos($request['image'], '.jpeg') !== false || strpos($request['image'], '.webp') !== false) {
                $photo = $request['image'];
            } else {
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $filename = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $photo = date('His') . '-' . Str::random(10) . '.' . $extension;
                    $file->move(public_path('image/actor'), $photo);
                } else {
                    $photo = "";
                }
            }

            // Find the actor by ID
            $actor = Actor::findOrFail($request->id);

            // Update actor record
            $actor->update([
                'name' => $request->name,
                'image' => $photo,
                'seo_teg' => $seoTags,
                'birth_date' => $request->birth_date,
                'place_of_birth' => $request->place_of_birth,
                'description' => $request->description,
                'like' => $request->like,
                'dislike' => $request->dislike,
                'ranking' => $request->ranking,
                'gender' => $request->gender,
               
                'sorting_position' => $request->sorting_position,
                'aliases' => $request->aliases,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Actor updated successfully',
                'data' => $actor
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update actor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Delete an actor
    public function delete($id)
    {
        try {
            // Find the actor by ID
            $actor = Actor::findOrFail($id);

            // Delete the actor record
            $actor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Actor deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete actor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get all actors (sorted A to Z)
    public function getAll()
    {
        $actors = Actor::orderBy('name', 'asc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Actors retrieved successfully',
            'data' => $actors
        ], 200);
    }

    // Get paginated actors (latest first)
    public function getPaginated(Request $request)
    {
        $page = $request->get('page', 1); 
        $perPage = $request->get('pageSize', 7); 

        $actors = Actor::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Actors fetched successfully',
            'current_page' => $actors->currentPage(),
            'data' => $actors->items(),
            'total_records' => $actors->total()
        ]);
    }

    // Get an actor by ID
    public function getById($id)
    {
        $actor = Actor::find($id);

        if (!$actor) {
            return response()->json([
                'success' => false,
                'message' => 'Actor not found'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Actor retrieved successfully',
            'data' => $actor
        ], 200);
    }
}