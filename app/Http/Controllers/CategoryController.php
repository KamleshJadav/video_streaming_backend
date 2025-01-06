<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
    public function add(Request $request)
    {
        try { 
        $seoTags = json_decode($request->input('seo_teg'), true);
        $request->validate([
            'name' => 'required|string|max:50000',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp',
            'seo_teg' => 'nullable|json',
            'total_video' => 'nullable|string|max:50000',
            'category_star_rate' => 'nullable|numeric|min:0|max:5',
            'sorting_position' => 'nullable|integer',
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
                $file->move(public_path('image/category'), $photo);

            } else {
                $photo = "";
            }
        }
        
        $category = Category::create([
            'name' => $request->name,
            'image' => $photo,
            'seo_teg' => $seoTags,
            'total_video' => 0,
            'category_star_rate' => $request->category_star_rate,
            'sorting_position' => $request->sorting_position,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category added successfully',
            'data' => $category
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to add category',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    // Update a category
    public function update(Request $request)
    {
      
        try {
           
            $seoTags = json_decode($request->input('seo_teg'), true);
            $request->validate([
                'name' => 'required|string|max:50000',
                'seo_teg' => 'nullable|json',
                'category_star_rate' => 'nullable|numeric|min:0|max:5',
                'sorting_position' => 'nullable|integer',
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
                    $file->move(public_path('image/category'), $photo);
                } else {
                    $photo = "";
                }
            }
       
        $category = Category::find( $request->id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->update([
            'name' => $request->name,
            'image' => $photo,
            'seo_teg' => $seoTags,
            'category_star_rate' => $request->category_star_rate,
            'sorting_position' => $request->sorting_position,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update category',
            'message' => $e->getMessage(),
        ], 500);
    }
    }

    // Delete a category
    public function delete($id)
    {
        try{
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to delete category',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    // Get all categories (sorted A to Z)
    public function getAll()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ], 200);
    }

    // Get paginated categories (latest first)
    public function getPaginated(Request $request)
    {
        $page = $request->get('page', 1); 
        $perPage = $request->get('pageSize', 7); 

        $categories = Category::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Categories fetched successfully',
            'current_page' => $categories->currentPage(),
            'data' => $categories->items(),
            'total_records' => $categories->total()
        ]);
    }

    // Get a category by ID
    public function getById($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $category
        ], 200);
    }


}
