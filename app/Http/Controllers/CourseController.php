<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Http\Requests\CourseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('category')
            ->withCount('enrollments')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'slug' => $course->slug,
                    'duration' => $course->duration,
                    'image_url' => $course->image_url,
                    'video_url' => $course->video_url,
                    'status' => $course->status,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'monitor_id' => $course->monitor_id,
                    'monitor_name' => $course->monitor ? $course->monitor->name : null,
                    'category_id' => $course->category_id,
                    'category_name' => $course->category ? $course->category->name : null,
                    'students_count' => $course->enrollments_count ?? 0,
                ];
            });

        if ($courses->isEmpty()) {
            return response()->json([
                'message' => 'No courses found',
            ], 404);
        }

        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }


    public function show($id)
    {
        $course = Course::with('category')->find($id);
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


    public function store(CourseRequest $request)
    {

        $validatedData = $request->validated();
        $validatedData['monitor_id'] =  $request->user()->monitor->id;

        if ($request->hasFile('image')) {
            $validatedData['image_url'] = $request->file('image')->store('courses', 'public');
        }

        if ($request->hasFile('video')) {
            $validatedData['video_url'] = $request->file('video')->store('courses', 'public');
        }

        $validatedData['slug'] = Str::slug($validatedData['title'], '-');

        if ($request->hasFile('video')) {
            $videoPath = Storage::disk('public')->path($validatedData['video_url']);
            try {
                $getID3 = new \getID3;
                $fileInfo = $getID3->analyze($videoPath);

                if (isset($fileInfo['playtime_seconds'])) {
                    $validatedData['duration'] = round($fileInfo['playtime_seconds']);
                }
            } catch (\Exception $e) {
                Log::error('Error extracting video duration: ' . $e->getMessage());
            }
        }

        if (array_key_exists('image', $validatedData)) {
            unset($validatedData['image']);
        }

        if (array_key_exists('video', $validatedData)) {
            unset($validatedData['video']);
        }

        $course = Course::create($validatedData);

        return response()->json([
            'course' => $course,
            'message' => 'Course created successfully :)'
        ], 201);
    }


    public function update(CourseRequest $request, $id)
    {

        $course = Course::findOrFail($id);

        $validatedData = $request->validated();

        $course->fill($validatedData);

        if (isset($validatedData['title']) && $validatedData['title'] !== $course->title) {
            $course->slug = Str::slug($validatedData['title']);
        }

        if ($request->hasFile('image')) {
            if ($course->image_url) {
                Storage::disk('public')->delete($course->image_url);
            }
            $course->image_url = $request->file('image')->store('courses/images', 'public');
        }

        if ($request->hasFile('video')) {
            if ($course->video_url) {
                Storage::disk('public')->delete($course->video_url);
            }
            $course->video_url = $request->file('video')->store('courses/videos', 'public');
        }

        $course->save();

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course
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
    public function filterByMonitor()
    {
        $monitorId = Auth::user()->id;

        $courses = Course::where('monitor_id', $monitorId)->with('category')
            ->withCount('enrollments')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'slug' => $course->slug,
                    'duration' => $course->duration,
                    'image_url' => $course->image_url,
                    'video_url' => $course->video_url,
                    'status' => $course->status,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'monitor_id' => $course->monitor_id,
                    'monitor_name' => $course->monitor ? $course->monitor->name : null,
                    'category_id' => $course->category_id,
                    'category_name' => $course->category ? $course->category->name : null,
                    'students_count' => $course->enrollments_count ?? 0,
                ];
            });

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

    public function getCourses(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $courses = Course::with('category')->get();
        } else {
            $courses = Course::where('monitor_id', $user->id)
                ->with('category')
                ->get();
        }

        return response()->json([
            'courses' => $courses
        ]);
    }
}
