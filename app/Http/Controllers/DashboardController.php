<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Enrollment;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $quizStats = Quiz::where('candidate_id', $user->candidate->id)->get();
        $quizCount = $quizStats->count();
        $bestScore = $quizStats->max('score') ?? 0;
        $avgScore = $quizCount > 0 ? round($quizStats->avg('score'), 1) : 0;
        $recentScore = $quizStats->sortByDesc('created_at')->first()?->score ?? 0;

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course')
            ->get();

        $coursesWatched = $enrollments->count();

        $inProgress = 0;
        $completed = 0;
        $recentCourses = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (!$course) continue;

            $progress = [
                'id' => $course->id,
                'title' => $course->title,
                'progress' => $enrollment->status,
                'last_accessed' => $enrollment->updated_at
            ];

            if ($enrollment->status === 'completed') {
                $completed++;
            } else {
                $inProgress++;
            }

            $recentCourses[] = $progress;
        }

        usort($recentCourses, function($a, $b) {
            return $b['last_accessed'] <=> $a['last_accessed'];
        });

        $recentCourses = array_slice($recentCourses, 0, 3);

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email
            ],
            'stats' => [
                'quiz_taken' => $quizCount,
                'courses_watched' => $coursesWatched
            ],
            'quiz' => [
                'recent_score' => $recentScore,
                'best_score' => $bestScore,
                'average_score' => $avgScore
            ],
            'courses' => [
                'in_progress' => $inProgress,
                'completed' => $completed,
                'recent_courses' => $recentCourses
            ]
        ]);
    }
}
