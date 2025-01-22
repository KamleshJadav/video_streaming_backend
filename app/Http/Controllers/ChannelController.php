<?php

// app/Http/Controllers/ChannelController.php
namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelController extends Controller
{
    // Add a new channel
    public function add(Request $request)
    {
        try {
            // Decode SEO tags if present
            $seoTags = json_decode($request->input('seo_teg'), true);

            // Validate the request data
            $request->validate([
                'name' => 'required|string|max:50000',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp',
                'seo_teg' => 'nullable|json',
                'subscriber' => 'nullable|numeric',
                'description' => 'nullable|string',
                'like' => 'nullable|integer',
                'dislike' => 'nullable|integer',
                'ratting' => 'nullable|numeric|min:0|max:5',
                'sorting_position' => 'nullable|integer',
                'total_view' => 'nullable|integer',
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
                    $file->move(public_path('image/channel'), $photo);
                } else {
                    $photo = "";
                }
            }

            // Create a new channel
            $channel = Channel::create([
                'name' => $request->name,
                'subscriber' => $request->subscriber,
                'description' => $request->description,
                'image' => $photo,
                'seo_teg' => $seoTags,
                'like' => $request->like,
                'dislike' => $request->dislike,
                'ratting' => $request->ratting,
                'sorting_position' => $request->sorting_position,
                'total_image' => 0,
                'total_video' => 0,
                'total_view' => $request->total_view,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Channel added successfully',
                'data' => $channel
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to add channel',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing channel
    public function update(Request $request)
    {
        try {
            // Decode SEO tags if present
            $seoTags = json_decode($request->input('seo_teg'), true);

            // Validate the request data
            $request->validate([
                'id' => 'required|integer|exists:channels,id',
                'name' => 'required|string|max:50000',
                'seo_teg' => 'nullable|json',
                'subscriber' => 'nullable|string',
                'description' => 'nullable|string',
                'like' => 'nullable|integer',
                'dislike' => 'nullable|integer',
                'ratting' => 'nullable|numeric|min:0|max:5',
                'sorting_position' => 'nullable|integer',
                'total_view' => 'nullable|integer',
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
                    $file->move(public_path('image/channel'), $photo);
                } else {
                    $photo = "";
                }
            }

            // Find the channel by ID
            $channel = Channel::findOrFail($request->id);

            // Update the channel
            $channel->update([
                'name' => $request->name,
                'subscriber' => $request->subscriber,
                'description' => $request->description,
                'image' => $photo,
                'seo_teg' => $seoTags,
                'like' => $request->like,
                'dislike' => $request->dislike,
                'ratting' => $request->ratting,
                'sorting_position' => $request->sorting_position,
                'total_view' => $request->total_view,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Channel updated successfully',
                'data' => $channel
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update channel',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a channel
    public function delete($id)
    {
        try {
            // Find the channel by ID
            $channel = Channel::findOrFail($id);

            // Delete the channel
            $channel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Channel deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete channel',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get all channels
    public function getAll()
    {
        $channels = Channel::orderBy('name', 'asc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Channels retrieved successfully',
            'data' => $channels
        ], 200);
    }

    // Get paginated channels
    public function getPaginated(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('pageSize', 7);

        $channels = Channel::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Channels fetched successfully',
            'current_page' => $channels->currentPage(),
            'data' => $channels->items(),
            'total_records' => $channels->total()
        ]);
    }

    // Get channel by ID
    public function getById($id)
    {
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Channel not found'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Channel retrieved successfully',
            'data' => $channel
        ], 200);
    }
}
