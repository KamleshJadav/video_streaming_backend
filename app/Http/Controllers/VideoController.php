<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Category;
use App\Models\Actor;
use App\Models\Channel;

class VideoController extends Controller
{
    // Add a new video
    public function add(Request $request)
    {
        try {
            $seoTags = json_decode($request->input('seo_teg'), true);
            $actorIds = json_decode($request->input('actor_id'), true);

            $request->validate([
                'title' => 'required|string|max:5000',
                'video' => 'required|string|max:5000',
                'seo_teg' => 'nullable|json',
                'actor_id' => 'nullable|json',
                'description' => 'nullable|string',
                'category_id' => 'nullable|string',
                'channel_id' => 'nullable|string',
                'views' => 'nullable|integer',
                'likes' => 'nullable|integer',
            ]);

            // Create a new video
            $video = Video::create([
                'title' => $request->title,
                'video' => $request->video,
                'seo_teg' => $seoTags,
                'actor_id' => $actorIds,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'channel_id' => $request->channel_id,
                'views' => $request->views,
                'likes' => $request->likes,
            ]);

            // Update total_video counts
            $this->updateTotalCounts($request->category_id, $actorIds, $request->channel_id);

            return response()->json([
                'success' => true,
                'message' => 'Video added successfully',
                'data' => $video,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to add video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Update an existing video
    public function update(Request $request)
    {
        try {
            $seoTags = json_decode($request->input('seo_teg'), true);
            $actorIds = json_decode($request->input('actor_id'), true);

            $request->validate([
                'id' => 'required|exists:videos,id',
                'title' => 'required|string|max:5000',
                'video' => 'required|string|max:5000',
                'seo_teg' => 'nullable|json',
                'actor_id' => 'nullable|json',
                'description' => 'nullable|string',
                'category_id' => 'nullable|string',
                'channel_id' => 'nullable|string',
                'views' => 'nullable|integer',
                'likes' => 'nullable|integer',
            ]);

            // Find the video by ID
            $video = Video::findOrFail($request->id);

            // Previous associations
            $previousCategory = $video->category_id;
            $previousActorIds = $video->actor_id;
            $previousChannel = $video->channel_id;

            // Update the video
            $video->update([
                'title' => $request->title,
                'video' => $request->video,
                'seo_teg' => $seoTags,
                'actor_id' => $actorIds,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'channel_id' => $request->channel_id,
                'views' => $request->views,
                'likes' => $request->likes,
            ]);

            // Update total_video counts
            $this->updateTotalCounts($request->category_id, $actorIds, $request->channel_id, $previousCategory, $previousActorIds, $previousChannel);

            return response()->json([
                'success' => true,
                'message' => 'Video updated successfully',
                'data' => $video,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete a video
    public function delete($id)
    {
        try {
            $video = Video::findOrFail($id);

            // Previous associations
            $previousCategory = $video->category_id;
            $previousActorIds = $video->actor_id;
            $previousChannel = $video->channel_id;

            // Delete the video
            $video->delete();

            // Update total_video counts
            $this->updateTotalCounts(null, null, null, $previousCategory, $previousActorIds, $previousChannel);

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete video',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Helper method to update total_video counts
    private function updateTotalCounts($newCategory, $newActorIds, $newChannel, $oldCategory = null, $oldActorIds = null, $oldChannel = null)
    {
        // Update old category total_video
        if ($oldCategory && $oldCategory !== $newCategory) {
            Category::where('id', $oldCategory)->decrement('total_video');
        }

        // Update new category total_video
        if ($newCategory && $newCategory !== $oldCategory) {
            Category::where('id', $newCategory)->increment('total_video');
        }

        // Update old channel total_video
        if ($oldChannel && $oldChannel !== $newChannel) {
            Channel::where('id', $oldChannel)->decrement('total_video');
        }

        // Update new channel total_video
        if ($newChannel && $newChannel !== $oldChannel) {
            Channel::where('id', $newChannel)->increment('total_video');
        }

        // Update actor total_video
        $oldActorIds = $oldActorIds ?: [];
        $newActorIds = $newActorIds ?: [];

        foreach (array_diff($oldActorIds, $newActorIds) as $actorId) {
            Actor::where('id', $actorId)->decrement('total_video');
        }

        foreach (array_diff($newActorIds, $oldActorIds) as $actorId) {
            Actor::where('id', $actorId)->increment('total_video');
        }
    }

    // Get paginated actors (latest first)
    public function getPaginated(Request $request)
    {
        $page = $request->get('page', 1); 
        $perPage = $request->get('pageSize', 7); 

        $videos = Video::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        
            $formattedVideos = $videos->map(function ($video) {
                // Parse the actor IDs from the video table
                // $actorIds = json_decode($video->actor_id, true);
        
                // Fetch actor names based on the IDs
                $actorNames = Actor::whereIn('id',$video->actor_id)->get(['id', 'name']);
        
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'video' => $video->video,
                    'actors' => $actorNames, // Actor names
                    'category_name' => $video->category->name, 
                    'category_id' => $video->category_id, 
                    'channel_name' => $video->channel->name,  
                    'channel_id' => $video->channel_id,  
                    'views' => $video->views,
                    'likes' => $video->likes,
                    'description' => $video->description,
                    'seo_teg' => $video->seo_teg,
                    'created_at' => $video->created_at,
                    'updated_at' => $video->updated_at,
                ];
            });
        
            return response()->json([
                'success' => true,
                'message' => 'Videos fetched successfully',
                'current_page' => $videos->currentPage(),
                'data' => $formattedVideos,
                'total_records' => $videos->total()
            ]);
    }

    // Get an actor by ID
    public function getById($id)
    {
        $actor = Video::find($id);

        if (!$actor) {
            return response()->json([
                'success' => false,
                'message' => 'Actor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Actor retrieved successfully',
            'data' => $actor
        ], 200);
    }

    public function getAll()
    {
        $videos = Video::select('id', 'name')->orderBy('name', 'asc')->get();
        $formattedVideos = $videos->map(function ($video) {
            $actorNames = Actor::whereIn('id',$video->actor_id)->get(['id', 'name']);
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video' => $video->video,
                'actors' => $actorNames, // Actor names
                'category_name' => $video->category->name, 
                'category_id' => $video->category_id, 
                'channel_name' => $video->channel->name,  
                'channel_id' => $video->channel_id,  
                'views' => $video->views,
                'likes' => $video->likes,
                'description' => $video->description,
                'seo_teg' => $video->seo_teg,
                'created_at' => $video->created_at,
                'updated_at' => $video->updated_at,
            ];
        });
        return response()->json([
            'success' => true,
            'message' => 'Videos retrieved successfully',
            'data' => $formattedVideos
        ], 200);
    }
}
