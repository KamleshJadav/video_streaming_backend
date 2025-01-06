<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    // Add a category
    public function add(Request $request)
    {
        try { 

        $request->validate([
            'name' => 'required|string|max:50000',
            'redirect_url' => 'required|string|max:50000',
            'sorting_position' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp',
            
        ]);

        if (strpos($request['image'], '.jpg') !== false || strpos($request['image'], '.png') !== false || strpos($request['image'], '.jpeg') !== false || strpos($request['image'], '.webp') !== false) {
            $photo = $request['image'];
        } else {
            if ($request->hasFile('image'))
            {
                $file      = $request->file('image');
                $filename  = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $photo   = date('His').'-'.Str::random(10).'.'.$extension;
                $file->move(public_path('image/banner'), $photo);

            } else {
                $photo = "";
            }
        }
        
        $banner_create = Banner::create([
            'name' => $request->name,
            'redirect_url' => $request->redirect_url,
            'is_active' => (int)$request->is_active,
            'sorting_position' => $request->sorting_position,
            'image' => $photo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner added successfully',
            'data' => $banner_create
        ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to add banner',
                'message' => $e->getMessage()
            ], 500);
        }
    }
 
    // Update a category
    public function update(Request $request)
    {
    
        try {
        
            $request->validate([
            'name' => 'required|string|max:50000',
            'redirect_url' => 'required|string|max:50000',
            'sorting_position' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            ]);
            if (strpos($request['image'], '.jpg') !== false || strpos($request['image'], '.png') !== false || strpos($request['image'], '.jpeg') !== false || strpos($request['image'], '.webp') !== false) {
                $photo = $request['image'];
            } else {
                if ($request->hasFile('image'))
                {
                    $file      = $request->file('image');
                    $filename  = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $photo   = date('His').'-'.Str::random(10).'.'.$extension;
                    $file->move(public_path('image/banner'), $photo);
                } else {
                    $photo = "";
                }
            }
    
        $bannerData = Banner::find( $request->id);

        if (!$bannerData) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        $bannerData->update([
        'name' => $request->name,
        'redirect_url' => $request->redirect_url,
        'is_active' => (int)$request->is_active,
        'sorting_position' => $request->sorting_position,
        'image' => $photo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => $bannerData
        ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update banner',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
 
    // Delete a category
    public function delete($id)
    {
        try{
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully'
        ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete banner',
                'message' => $e->getMessage()
            ], 500);
        }
    }
 
    // Get all categories (sorted sorting_position)
    public function getAll()
    {
        $banner = Banner::orderBy('sorting_position', 'asc')
                ->get();
                
        return response()->json([
            'success' => true,
            'message' => 'Banner retrieved successfully',
            'data' => $banner
        ], 200);
    }

    // Get paginated categories (latest first)
    public function getPaginated(Request $request)
    {
        $page = $request->get('page', 1); 
        $perPage = $request->get('pageSize', 7); 

        $Banner = Banner::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Banners fetched successfully',
            'current_page' => $Banner->currentPage(),
            'data' => $Banner->items(),
            'total_records' => $Banner->total()
        ]);
    }
 
    // Get a category by ID
    public function getById($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $banner
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        try {
            // Validate only 'is_active'
            $request->validate([
                'is_active' => 'required|boolean',  // Ensure the 'is_active' field is required and a boolean value
            ]);

            // Find the banner by ID
            $bannerData = Banner::find($request->id);

            // If the banner is not found, return an error
            if (!$bannerData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found',
                ], 404);
            }

            // Update only the 'is_active' field
            $bannerData->update([
                'is_active' => (int) $request->is_active,  // Ensure 'is_active' is cast to an integer
            ]);

            // Return success response with updated data
            return response()->json([
                'success' => true,
                'message' => 'Banner status updated successfully',
                'data' => $bannerData,
            ], 200);

        } catch (\Exception $e) {
            // Catch any exceptions and return a failure response
            return response()->json([
                'success' => false,
                'error' => 'Failed to update banner status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function  getAllActiveBanner() {
        try {
            $banner = Banner::where('is_active', 1)->orderBy('sorting_position', 'asc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Banner retrieved successfully',
            'data' => $banner
        ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieved banner',
                'message' => $e->getMessage()
            ], 200);
        }
       
    }

}
