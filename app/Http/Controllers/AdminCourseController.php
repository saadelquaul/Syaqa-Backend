<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCourseController extends Controller
{

    public function index(Request $request)
    {
        $query = Course::with(['category:id,name', 'monitor:id,user_id', 'monitor.user:id,name'])->withCount('enrollments');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'courses' => $courses
        ]);
    }


    public function show($id)
    {
        $course = Course::with(['category', 'monitor'])->findOrFail($id);


        $studentsCount = $course->students()->count();
        $course->students_count = $studentsCount;

        return response()->json([
            'course' => $course
        ]);
    }


    public function updateStatus(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:inactive,active',
        ]);

        $course->status = $validated['status'];
        $course->save();

        return response()->json([
            'message' => 'Statut du cours mis à jour avec succès',
            'course' => $course
        ]);
    }


    public function destroy($id)
    {
        $course = Course::findOrFail($id);


        if ($course->image_url) {
            Storage::disk('public')->delete($course->image_url);
        }

        if ($course->video_url) {
            Storage::disk('public')->delete($course->video_url);
        }


        $course->students()->detach();

        $course->delete();

        return response()->json([
            'message' => 'Cours supprimé avec succès'
        ]);
    }
}
