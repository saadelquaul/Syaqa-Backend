<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{


    public function index()
    {
        $users = User::orderBy('created_at', 'desc')
            ->get();
        foreach ($users as $user) {
            if ($user->role === 'candidate') {
                $user->load('candidate:id,user_id');
            } elseif ($user->role === 'monitor') {
                $user->load('monitor:id,user_id');
            }
        }

        return response()->json([
            'users' => $users
        ]);
    }


    public function pendingCandidates()
    {
        $users = User::where('status', 'inactive')->where('role', 'candidate')->with('candidate')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }


    public function show($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'candidate') {
            $user->load('candidate')->load('candidate.document');
        } elseif ($user->role === 'monitor') {
            $user->load('monitor');
        }

        return response()->json([
            'user' => $user
        ]);
    }


    public function approveCondidate($id)
    {
        $user = User::findOrFail($id);


        $user->status = 'active';
        $user->save();

        return response()->json([
            'message' => 'Utilisateur approuvé avec succès',
            'user' => $user
        ]);
    }


    public function rejectCondidate($id)
    {
        $user = Candidate::findOrFail($id);
        $user->status = 'rejected';
        $user->save();

        return response()->json([
            'message' => 'Utilisateur rejeté avec succès',
            'user' => $user
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'address' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:active,inactive,rejected,graduated',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user
        ]);
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        if ($user->role === 'candidate' && $user->candidate) {
            $user->candidate->delete();
        } elseif ($user->role === 'monitor' && $user->monitor) {
            $user->monitor->delete();
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}
