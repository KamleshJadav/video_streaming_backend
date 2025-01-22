<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WishlistVideo;
use App\Models\Video;
use App\Models\Actor;
use App\Models\User;
class WishlistController extends Controller
{
  
    public function add(Request $request)
    {
        try {
            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'user_id' => 'required|exists:users,id',
            ]);
    
            $exists = WishlistVideo::where('user_id', $request->user_id)
                    ->where('video_id', $request->video_id)
                    ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video is already in the wishlist.',
                ], 200); 
            }

            WishlistVideo::create([
                'user_id' => $request->user_id,
                'video_id' => $request->video_id,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Video added to wishlist.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to add wishlist.',
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function remove(Request $request)
    {
        try {

            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'user_id' => 'required|exists:users,id',
            ]);
    
            $deleted = WishlistVideo::where('user_id', $request->user_id)
                ->where('video_id', $request->video_id)
                ->delete();
    
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Video removed from wishlist.',
                ], 200);
            }
    
            return response()->json([
                'success' => false,
                'message' => 'Video not found in wishlist.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to remove from wishlist.',
            ], 500);
        }
    }

    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'video_id' => 'required|exists:videos,id',
                'user_id' => 'required|exists:users,id',
            ]);

            $wishlistItem = WishlistVideo::where('user_id', $request->user_id)
                ->where('video_id', $request->video_id)
                ->first();

            if ($wishlistItem) {

                $wishlistItem->delete();

                return response()->json([
                    'success' => true,
                    'isInWishlist' => false,
                    'message' => 'Video removed from wishlist.',
                ], 200);

            } else {

                WishlistVideo::create([
                    'user_id' => $request->user_id,
                    'video_id' => $request->video_id,
                ]);

                return response()->json([
                    'success' => true,
                    'isInWishlist' => true,
                    'message' => 'Video added to wishlist.',
                ], 200);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to toggle wishlist.',
            ], 500);
        }
    }
  
    public function getPaginated(Request $request)
    {
        try {
            $page = $request->page;
            $perPage = $request->pageSize;
            $categoryId = $request->category_id; 
    
            // Build the query
            $query = WishlistVideo::where('user_id', $request->user_id)
            ->whereHas('video', function ($query) use ($categoryId) {
                if ($categoryId && $categoryId !== 'all') {
                    $query->where('category_id', $categoryId);
                }
            })
            ->with('video');
    
            // Paginate the results
            $wishlistVideos = $query->paginate($perPage, ['*'], 'page', $page);
    
            // Format the videos
            $formattedVideos = $wishlistVideos->map(function ($video) {
                $video->video->wishlist_id = $video->id;
                $video->video->video_id = $video->video_id;
                $video->video->user_id = $video->user_id;
                return $video->video;
            });
    
            // Return the response
            return response()->json([
                'success' => true,
                'message' => 'Wishlist videos fetched successfully',
                'current_page' => $wishlistVideos->currentPage(),
                'data' => $formattedVideos,
                'total_records' => $wishlistVideos->total(),
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch wishlist videos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAll(Request $request)
    {
        try {
            $wishlistVideos = WishlistVideo::where('user_id', $request->user_id)
                ->with(['video.category', 'video.channel']) // Eager load category and channel relationships
                ->get();
    
            $formattedVideos = $wishlistVideos->map(function ($wishlistVideo) {
                $video = $wishlistVideo->video;
    
                $actorIds = $video->actor_id;
                $actors = Actor::whereIn('id', $actorIds)->pluck('name');
    
                return [
                    'wishlist_id' => $wishlistVideo->id,
                    'video_id' => $video->id,
                    'title' => $video->title,
                    'video_url' => $video->video,
                    'channel_id' => $video->channel_id,
                    'category_id' => $video->category_id,
                    'thumb_name' => $video->thumb_name,
                    'description' => $video->description,
                    'category_name' => $video->category->name ?? null,
                    'channel_name' => $video->channel->name ?? null,
                    'actors' => $actors,
                ];
            });
    
            // Return the response
            return response()->json([
                'success' => true,
                'message' => 'Wishlist videos fetched successfully',
                'data' => $formattedVideos,
                'total_records' => $formattedVideos->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch wishlist videos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
