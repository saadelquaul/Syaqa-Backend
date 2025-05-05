<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;


class QuizController extends Controller
{

    public function index(Request $request)
    {
        $quizzes = Quiz::with(['candidate:id,name'])->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'quizzes' => $quizzes
        ]);
    }

    public function show($id)
    {
        $quiz = Quiz::with(['candidate:id,name'])->findOrFail($id);

        return response()->json([
            'quiz' => $quiz
        ]);
    }
    public function candidateQuizzes($candidateId)
    {
        $quizzes = Quiz::where('candidate_id', $candidateId)->with(['candidate:id,name'])->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'quizzes' => $quizzes
        ]);
    }

    public function store(Request $request)
    {

            $validated = $request->validate([
                'correct_answers' => 'required|integer',
                'incorrect_answers' => 'required|integer',
                'total_questions' => 'required|integer',
                'score' => 'required|integer',
            ]);

            $candidateId = $request->user()->candidate->id;
            $quiz = Quiz::create([
                'correct_answers' => $validated['correct_answers'],
                'incorrect_answers' => $validated['incorrect_answers'],
                'total_questions' => $validated['total_questions'],
                'score' => $validated['score'],
                'candidate_id' => $candidateId,
            ]);
            if (!$quiz) {
                return response()->json([
                    'message' => 'Failed to create quiz'
                ], 500);
            }

            return response()->json([
                'message' => 'Quiz created successfully',
                'quiz' => $quiz
            ], 201);
    }

    public function generateQuiz(Request $request)
    {
        // Get authenticated user's candidate
        $user = $request->user();
        $candidate = $user->candidate;

        if (!$candidate) {
            return response()->json([
                'message' => 'User is not a candidate'
            ], 403);
        }

        $questionCount = $request->input('question_count', 40);
        $questions = QuizQuestion::inRandomOrder()
            ->take($questionCount)
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'options' => [
                        'option_a' => $question->option_a,
                        'option_b' => $question->option_b,
                        'option_c' => $question->option_c,
                        'option_d' => $question->option_d,
                    ],
                    'correct_answer' => $question->correct_answer,
                ];
            });

        return response()->json([
            'questions' => $questions,
            'message' => 'Quiz generated successfully'
        ]);
    }


    public function history(Request $request)
    {
        $user = $request->user();
        $quizzes = Quiz::where('candidate_id', $user->candidate->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'date' => $quiz->created_at->format('Y-m-d H:i:s'),
                    'correct_answers' => $quiz->correct_answers,
                    'incorrect_answers' => $quiz->incorrect_answers,
                    'total_questions' => $quiz->total_questions,
                    'score' => $quiz->score,
                ];
            });

        return response()->json([
            'quizzes' => $quizzes,
            'message' => 'Quiz history retrieved successfully'
        ]);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();
        $candidateId = $user->candidate->id;

        // Get quiz statistics
        $quizStats = Quiz::where('candidate_id', $candidateId)->get();
        $totalQuizzes = $quizStats->count();

        if ($totalQuizzes === 0) {
            return response()->json([
                'total_quizzes' => 0,
                'best_score' => 0,
                'average_score' => 0,
                'recent_score' => 0,
                'message' => 'No quiz attempts found'
            ]);
        }

        // Calculate statistics
        $bestScore = $quizStats->max('score') ?? 0;
        $avgScore = round($quizStats->avg('score'), 1);
        $recentScore = $quizStats->sortByDesc('created_at')->first()?->score ?? 0;

        // Get performance over time
        $monthlyStats = $quizStats
            ->groupBy(function($quiz) {
                return $quiz->created_at->format('Y-m');
            })
            ->map(function($month) {
                return round($month->avg('score'), 1);
            });

        return response()->json([
            'total_quizzes' => $totalQuizzes,
            'best_score' => $bestScore,
            'average_score' => $avgScore,
            'recent_score' => $recentScore,
            'monthly_performance' => $monthlyStats,
            'message' => 'Quiz statistics retrieved successfully'
        ]);
    }

    public function results(Request $request, $quizId)
    {
        $user = $request->user();
        $quiz = Quiz::where('id', $quizId)
            ->where('candidate_id', $user->candidate->id)
            ->first();

        if (!$quiz) {
            return response()->json([
                'message' => 'Quiz not found or not authorized'
            ], 404);
        }

        // For a real implementation, we would need a table to store the specific answers
        // But for now, we'll just return the summary
        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'date' => $quiz->created_at->format('Y-m-d H:i:s'),
                'correct_answers' => $quiz->correct_answers,
                'incorrect_answers' => $quiz->incorrect_answers,
                'total_questions' => $quiz->total_questions,
                'score' => $quiz->score,
                // In a complete implementation, we would include:
                // 'questions' => [...] with user answers and correct answers
            ],
            'message' => 'Quiz results retrieved successfully'
        ]);
    }
}
