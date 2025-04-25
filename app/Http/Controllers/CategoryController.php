<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;




class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('courses')->get();

        return response()->json([
            'success' => 'true',
            'data' => $categories? $categories : []
        ]);
    }

    public function store(CategoryRequest $request)
    {

        $validated = $request->validated();


        DB::beginTransaction();
        try {

            $category = Category::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
            ]);

            if ($request->hasFile('image')) {
                $category->image = $request->file('image')->store('categories', 'public');
            }

            $category->save();

            DB::commit();

            return response()->json([
                'success' => 'true',
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => 'false',
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function show($id)
    {
        $category = Category::with('courses')->find($id);

        if (!$category) {
            return response()->json([
                'success' => 'false',
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => 'true',
            'data' => $category
        ]);
    }
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => 'false',
                'message' => 'Category not found'
            ], 404);
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {

            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
            $category->description = $validated['description'] ?? null;

            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($category->image);
                $category->image = $request->file('image')->store('categories', 'public');
            }

            $category->save();

            DB::commit();

            return response()->json([
                'success' => 'true',
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => 'false',
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => 'false',
                'message' => 'Category not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            Storage::disk('public')->delete($category->image);
            $category->delete();

            DB::commit();

            return response()->json([
                'success' => 'true',
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => 'false',
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
