<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;



class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('courses')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
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
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
