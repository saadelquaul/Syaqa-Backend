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
            ->get(['id', 'name', 'email', 'role', 'status', 'profile_picture', 'created_at']);

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Get pending users
     */
    public function pendingCandidates()
    {
        $users = Candidate::where('status', 'inactive')->with('user:id,name,email,role')->with('document')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'status', 'profile_picture', 'created_at']);

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Show user details
     */
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

    /**
     * Approve a user
     */
    public function approveCondidate($id)
    {
        $user = Candidate::findOrFail($id);

        $user->status = 'active';
        $user->save();

        return response()->json([
            'message' => 'Utilisateur approuvé avec succès',
            'user' => $user
        ]);
    }

    /**
     * Reject a user
     */
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

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|in:admin,monitor,candidate',
            'status' => 'sometimes|in:active,pending,suspended',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user
        ]);
    }

    /**
     * Delete user
     */
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
