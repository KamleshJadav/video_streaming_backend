<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Images;
use App\Models\Category;
use App\Models\Actor;
use App\Models\Channel;

class ImageController extends Controller
{
     // Add a new video
     public function add(Request $request)
     {
         try {
             $seoTags = json_decode($request->input('seo_teg'), true);
             $actorIds = json_decode($request->input('actor_id'), true);
             $images = json_decode($request->input('images'), true);
 
             $request->validate([
                 'title' => 'required|string|max:5000',
                 'seo_teg' => 'nullable|json',
                 'images' => 'nullable|json',
                 'actor_id' => 'nullable|json',
                 'description' => 'nullable|string',
                 'category_id' => 'nullable|string',
                 'channel_id' => 'nullable|string',
                 'views' => 'nullable|integer',
                 'likes' => 'nullable|integer',
             ]);
 
             // Create a new video
             $saveimage = Images::create([
                 'title' => $request->title,
                 'images' => $images,
                 'seo_teg' => $seoTags,
                 'actor_id' => $actorIds,
                 'description' => $request->description,
                 'category_id' => $request->category_id,
                 'channel_id' => $request->channel_id,
                 'views' => $request->views,
                 'likes' => $request->likes,
             ]);
 
             // Update total_image counts
             $this->updateTotalCounts($request->category_id, $actorIds, $request->channel_id);
 
             return response()->json([
                 'success' => true,
                 'message' => 'Images added successfully',
                 'data' => $saveimage,
             ], 201);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'error' => 'Failed to add images',
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
             $images = json_decode($request->input('images'), true);
 
             $request->validate([
                 'id' => 'required|exists:videos,id',
                 'title' => 'required|string|max:5000',
                 'seo_teg' => 'nullable|json',
                 'images' => 'nullable|json',
                 'actor_id' => 'nullable|json',
                 'description' => 'nullable|string',
                 'category_id' => 'nullable|string',
                 'channel_id' => 'nullable|string',
                 'views' => 'nullable|integer',
                 'likes' => 'nullable|integer',
             ]);
 
             // Find the video by ID
             $saveimage = Images::findOrFail($request->id);
 
             // Previous associations
             $previousCategory = $saveimage->category_id;
             $previousActorIds = $saveimage->actor_id;
             $previousChannel = $saveimage->channel_id;
 
             // Update the video
             $saveimage->update([
                 'title' => $request->title,
                 'video' => $request->video,
                 'images' => $images,
                 'seo_teg' => $seoTags,
                 'actor_id' => $actorIds,
                 'description' => $request->description,
                 'category_id' => $request->category_id,
                 'channel_id' => $request->channel_id,
                 'views' => $request->views,
                 'likes' => $request->likes,
             ]);
 
             // Update total_image counts
             $this->updateTotalCounts($request->category_id, $actorIds, $request->channel_id, $previousCategory, $previousActorIds, $previousChannel);
 
             return response()->json([
                 'success' => true,
                 'message' => 'Images updated successfully',
                 'data' => $saveimage,
             ], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'error' => 'Failed to update images',
                 'message' => $e->getMessage(),
             ], 500);
         }
     }
 
     // Delete a video
     public function delete($id)
     {
         try {
             $saveimage = Images::findOrFail($id);
 
             // Previous associations
             $previousCategory = $saveimage->category_id;
             $previousActorIds = $saveimage->actor_id;
             $previousChannel = $saveimage->channel_id;
 
             // Delete the video
             $saveimage->delete();
 
             // Update total_image counts
             $this->updateTotalCounts(null, null, null, $previousCategory, $previousActorIds, $previousChannel);
 
             return response()->json([
                 'success' => true,
                 'message' => 'Images deleted successfully',
             ], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'error' => 'Failed to delete images',
                 'message' => $e->getMessage(),
             ], 500);
         }
     }
 
     // Helper method to update total_image counts
     private function updateTotalCounts($newCategory, $newActorIds, $newChannel, $oldCategory = null, $oldActorIds = null, $oldChannel = null)
     {
         // Update old category total_image
         if ($oldCategory && $oldCategory !== $newCategory) {
             Category::where('id', $oldCategory)->decrement('total_image');
         }
 
         // Update new category total_image
         if ($newCategory && $newCategory !== $oldCategory) {
             Category::where('id', $newCategory)->increment('total_image');
         }
 
         // Update old channel total_image
         if ($oldChannel && $oldChannel !== $newChannel) {
             Channel::where('id', $oldChannel)->decrement('total_image');
         }
 
         // Update new channel total_image
         if ($newChannel && $newChannel !== $oldChannel) {
             Channel::where('id', $newChannel)->increment('total_image');
         }
 
         // Update actor total_image
         $oldActorIds = $oldActorIds ?: [];
         $newActorIds = $newActorIds ?: [];
 
         foreach (array_diff($oldActorIds, $newActorIds) as $actorId) {
             Actor::where('id', $actorId)->decrement('total_image');
         }
 
         foreach (array_diff($newActorIds, $oldActorIds) as $actorId) {
             Actor::where('id', $actorId)->increment('total_image');
         }
     }
 
     // Get paginated actors (latest first)
     public function getPaginated(Request $request)
     {
         $page = $request->get('page', 1); 
         $perPage = $request->get('pageSize', 7); 
 
         $images = Images::orderBy('created_at', 'desc')
             ->paginate($perPage, ['*'], 'page', $page);
 
         
             $formattedVideos = $images->map(function ($video) {
                
                 $actorNames = Actor::whereIn('id',$video->actor_id)->get(['id', 'name']);
         
                 return [
                     'id' => $video->id,
                     'title' => $video->title,
                     'images' => $video->images,
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
                 'message' => 'Images fetched successfully',
                 'current_page' => $images->currentPage(),
                 'data' => $formattedVideos,
                 'total_records' => $images->total()
             ]);
     }
 
     // Get an actor by ID
     public function getById($id)
     {
         $actor = Images::find($id);
 
         if (!$actor) {
             return response()->json([
                 'success' => false,
                 'message' => 'Images not found'
             ], 404);
         }
 
         return response()->json([
             'success' => true,
             'message' => 'Images retrieved successfully',
             'data' => $actor
         ], 200);
     }
 
     public function getAll()
     {
         $videos = Images::select('id', 'name')->orderBy('name', 'asc')->get();
         $formattedVideos = $videos->map(function ($video) {
             $actorNames = Actor::whereIn('id',$video->actor_id)->get(['id', 'name']);
             return [
                 'id' => $video->id,
                 'title' => $video->title,
                 'video' => $video->video,
                 'images' => $video->images,
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
             'message' => 'Images retrieved successfully',
             'data' => $formattedVideos
         ], 200);
     }
}
