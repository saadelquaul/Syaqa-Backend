<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;


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
        $candidateId = Auth::user()->candidate->id;
        $quiz = Quiz::create([
            $validated,
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
}
