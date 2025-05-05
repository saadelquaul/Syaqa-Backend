<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function show(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role === 'candidate') {
                $user->load(['candidate', 'candidate.document']);
            } elseif ($user->role === 'monitor') {
                $user->load('monitor');
            }

            if ($user->profile_picture) {
                $user->profile_picture_url = url(Storage::url($user->profile_picture));
            }

            return response()->json([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des données utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'current_password' => 'nullable|string|required_with:password',
                'password' => 'nullable|string|min:8|confirmed',
                'password_confirmation' => 'nullable|string',
            ]);
            
            if (isset($validatedData['password']) && $validatedData['password']) {
                if (!Hash::check($validatedData['current_password'], $user->password)) {
                    return response()->json([
                        'errors' => [
                            'current_password' => ['Le mot de passe actuel est incorrect']
                        ]
                    ], 422);
                }

                $user->password = Hash::make($validatedData['password']);
            }

            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];

            if (isset($validatedData['phone_number'])) {
                $user->phone_number = $validatedData['phone_number'];
            }

            if (isset($validatedData['address'])) {
                $user->address = $validatedData['address'];
            }

            $user->save();

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            $user = $request->user();

            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
            $user->save();

            return response()->json([
                'message' => 'Photo de profil mise à jour avec succès',
                'profile_picture' => $path,
                'profile_picture_url' => url(Storage::url($path))
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating profile picture: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de la photo de profil',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
