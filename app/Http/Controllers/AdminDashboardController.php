<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Booking;
use App\Models\Candidate;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{

    public function index()
    {

        $usersCount = User::count();
        $coursesCount = Course::count();
        $pendingUsersCount = Candidate::where('status', 'inactive')->count();
        $bookingsCount = Booking::where('status','confirmed')->count();

        return response()->json([
            'users_count' => $usersCount,
            'courses_count' => $coursesCount,
            'pending_users_count' => $pendingUsersCount,
            'bookings_count' => $bookingsCount,
        ]);
    }


    public function recentRegistrations()
    {
        $registrations = Candidate::where('status', 'inactive')->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'registrations' => $registrations
        ]);
    }

    
    public function recentCourses()
    {
        $courses = Course::with('category:id,name', 'monitor:id,name')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'status', 'category_id', 'monitor_id', 'image_url', 'created_at'])
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'status' => $course->status,
                    'category_name' => $course->category->name,
                    'monitor_name' => $course->monitor->name,
                    'image_url' => $course->image_url,
                    'created_at' => $course->created_at,
                ];
            });

        return response()->json([
            'courses' => $courses
        ]);
    }
}
