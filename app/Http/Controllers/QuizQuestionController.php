<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Storage;

class QuizQuestionController extends Controller
{
    public function index()
    {
        $questions = QuizQuestion::all();

        return response()->json([
            'data' => $questions,
            'message' => 'Questions retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'string|max:255',
            'option_d' => 'string|max:255',
            'correct_answer' => 'required|in:option_a,option_b,option_c,option_d',
        ]);

        try {

            $question = QuizQuestion::create([
                'question' => $request->question,
                'option_a' => $request->option_a,
                'option_b' => $request->option_b,
                'option_c' => $request->option_c? $request->option_c : null,
                'option_d' => $request->option_d? $request->option_d : null,
                'correct_answer' => $request->correct_answer,
            ]);


            return response()->json([
                'message' => 'Question created successfully',
                'data' => $question,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $question = QuizQuestion::find($id);

        if (!$question) {
            return response()->json([
                'message' => 'Question not found'
            ], 404);
        }

        return response()->json([
            'data' => $question
        ]);
    }

    public function update(Request $request, $id)
    {
        $question = QuizQuestion::find($id);

        if (!$question) {
            return response()->json([
                'message' => 'Question not found'
            ], 404);
        }

        $request->validate([
            'question' => 'required|string|max:1000',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'string|max:255',
            'option_d' => 'string|max:255',
            'correct_answer' => 'required|in:option_a,option_b,option_c,option_d',
        ]);

        try {

            $question->update([
                'question' => $request->question,
                'option_a' => $request->option_a,
                'option_b' => $request->option_b,
                'option_c' => $request->option_c? $request->option_c : null,
                'option_d' => $request->option_d? $request->option_d : null,
                'correct_answer' => $request->correct_answer,
            ]);

            return response()->json([
                'message' => 'Question updated successfully',
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $question = QuizQuestion::find($id);

        if (!$question) {
            return response()->json([
                'message' => 'Question not found'
            ], 404);
        }

        try {

            $question->delete();

            return response()->json([
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
