<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WishlistVideo;
use App\Models\Video;
use App\Models\User;
class WishlistController extends Controller
{
    /**
     * Add a video to the wishlist.
     */
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
            //throw $th;
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
  
    public function getPaginated(Request $request)
    {
        try {
            $page =  $request->page;
            $perPage = $request->pageSize;
    
            $wishlistVideos = WishlistVideo::where('user_id',  $request->user_id)
                ->with('video') 
                ->paginate($perPage, ['*'], 'page', $page);
    
                $formattedVideos = $wishlistVideos->map(function ($video) {
                    $video->video->wishlist_id = $video->id;
                    $video->video->video_id = $video->video_id;
                    $video->video->user_id = $video->user_id;
                    return $video->video;
                   
                });
            return response()->json([
                'success' => true,
                'message' => 'Wishlist videos fetched successfully',
                'current_page' => $wishlistVideos->currentPage(),
                'data' =>$formattedVideos,
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
    
}
