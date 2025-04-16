<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Http\Requests\CourseRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }


    // public function show($id)
    // {
    //     $course = Course::find($id);
    //     if (!$course) {
    //         return response()->json([
    //             'message' => 'Course not found'
    //         ], 404);
    //     }
    //     return response()->json([
    //         'course' => $course,
    //         'message' => 'Course retrieved successfully'
    //     ]);
    // }


    public function store(CourseRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('courses', 'public');
        }

        $validatedData['slug'] = Str::slug($validatedData['title']);

        $course = Course::create($validatedData);

        return response()->json([
            'course' => $course,
            'message' => 'Course created successfully'
        ], 201);
    }
    public function update(CourseRequest $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }

        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($course->image);
            $validatedData['image'] = $request->file('image')->store('courses', 'public');
        }

        $course->update($validatedData);

        return response()->json([
            'course' => $course,
            'message' => 'Course updated successfully'
        ]);
    }
    public function destroy($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }

        Storage::disk('public')->delete($course->image);

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $courses = Course::where('title', 'LIKE', "%{$query}%")->get();

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }
    public function filterByCategory($categoryId)
    {
        $courses = Course::where('category_id', $categoryId)->get();

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }
    public function filterByInstructor($instructorId)
    {
        $courses = Course::where('instructor_id', $instructorId)->get();

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }
    public function filterByStatus($status)
    {
        $courses = Course::where('status', $status)->get();

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }
    public function filterByDuration($duration)
    {
        $courses = Course::where('duration', $duration)->get();

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }
    public function filterBySlug($slug)
    {
        $course = Course::where('slug', $slug)->first();
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }

        return response()->json([
            'course' => $course,
            'message' => 'Course retrieved successfully'
        ]);
    }
    public function filterByTitle($title)
    {
        $courses = Course::where('title', 'LIKE', "%{$title}%")->get();

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }

}
