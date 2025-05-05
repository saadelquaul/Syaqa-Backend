<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Http\Requests\CourseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Enrollment;

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

    public function showCourse(Request $request, $courseId){

        $user = $request->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)->with('course')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Vous n\'êtes pas inscrit à ce cours'
            ], 403);
        }

        return response()->json([
            'enrollment' => [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'enrolled_at' => $enrollment->created_at,
                'completed_at' => $enrollment->completed_at,
            ],
            'course' => [
                'id' => $enrollment->course->id,
                'title' => $enrollment->course->title,
                'description' => $enrollment->course->description,
                'slug' => $enrollment->course->slug,
                'duration' => $enrollment->course->duration,
                'image_url' => $enrollment->course->image_url,
                'video_url' => $enrollment->course->video_url,
            ]
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
    public function filterByMonitor(Request $request)
    {
        $monitorId = $request->user()->monitor->id;

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

    public function enrolledCourses(Request $request) {
        $user = $request->user();

        $enrolledCourses = $user->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
            ->withPivot('status', 'enrolled_at', 'completed_at')
            ->with('category', 'monitor.user')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'slug' => $course->slug,
                    'duration' => $course->duration,
                    'image_url' => $course->image_url,
                    'status' => $course->status,
                    'category_name' => $course->category->name ?? null,
                    'monitor_name' => $course->monitor->user->name ?? null,
                    'enrollment_status' => $course->pivot->status,
                    'enrolled_at' => $course->pivot->enrolled_at,
                    'completed_at' => $course->pivot->completed_at,
                ];
            });

        return response()->json([
            'courses' => $enrolledCourses,
            'message' => 'Enrolled courses retrieved successfully'
        ]);
    }

    public function availableCourses(Request $request) {
        $user = $request->user();

        $enrolledCourseIds = $user->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
            ->pluck('courses.id');

        $availableCourses = Course::where('status', 'active')
            ->whereNotIn('id', $enrolledCourseIds)
            ->with('category', 'monitor.user')
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
                    'category_name' => $course->category->name ?? null,
                    'monitor_name' => $course->monitor->user->name ?? null,
                    'students_count' => $course->enrollments_count,
                ];
            });

        return response()->json([
            'courses' => $availableCourses
        ]);
    }

    public function enroll(Request $request, Course $course)
    {

        $user = $request->user();

    if ($course->status !== 'active') {
        return response()->json([
            'message' => 'This course is not available for enrollment'
        ], 403);
    }

    $existingEnrollment = $user->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
        ->where('courses.id', $course->id)
        ->first();

    if ($existingEnrollment) {
        return response()->json([
            'message' => 'You are already enrolled in this course'
        ], 409);
    }


    $enrollment = $user->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
        ->attach($course->id, [
            'status' => 'active',
            'enrolled_at' => now()
        ]);

    return response()->json([
        'message' => 'Successfully enrolled in course',
        'course' => [
            'id' => $course->id,
            'title' => $course->title
        ]
    ], 201);
    }

    public function updateEnrollment(Request $request, Course $course)
    {
        $user = $request->user();

        $enrollment = $user->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
            ->where('courses.id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course'
            ], 404);
        }

        $enrollment->pivot->status = 'completed';
        $enrollment->pivot->completed_at = now();
        $enrollment->pivot->save();

        return response()->json([
            'message' => 'Course completed successfully',
            'course' => [
                'id' => $course->id,
                'title' => $course->title
            ]
        ]);
    }
}
