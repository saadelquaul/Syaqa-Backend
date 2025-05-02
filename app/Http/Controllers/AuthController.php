<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $fields = $request->validated();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
                'profile_picture' => $fields['profile_picture'] ? $fields['profile_picture'] : null,
                'date_of_birth' => $fields['date_of_birth'],
                'address' => $fields['address'],
                'phone_number' => $fields['phone'],
            ]);

            $candidate = $user->candidate()->create([
                'license_type' => $fields['license_type'],
                'enrollment_date' => $fields['enrollment_date'],

            ]);

            $cinFile = $request->file('CIN');

            Document::create([
                'candidate_id' => $candidate->id,
                'CIN' => $cinFile->store('documents','public'),
                'cin_type' => $cinFile->extension(),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Compte candidat créé avec succès',
                'user' => $user,
                'condidate' => $candidate,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function monitorRegister(RegisterRequest $request)
    {
        $fields = $request->validated();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
                'role' => 'monitor',
                'profile_picture' => $fields['profile_picture']->store('profile_pictures', 'public'),
                'date_of_birth' => $fields['date_of_birth'],
                'address' => $fields['address'],
                'phone_number' => $fields['phone_number'],
                'status' => 'active',
            ]);

            $monitor = $user->monitor()->create([
                'license_number' => $fields['license_number'],
                'employment_date' => $fields['employment_date'],
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Compte moniteur créé avec succès',
                'user' => $user,
                'monitor' => $monitor,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $fields = $request->validated();

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        if ($user->role == 'candidate' && $user->candidate->status == 'inactive') {
            return response()->json([
                'message' => 'Votre compte est inactif sill vous plait attendez la validation de votre compte'
            ], 401);
        }

        $token = $user->createToken('Syaqa');
        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken
        ], 200);
    }

    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnecté'
        ], 200);
    }

    public function user(Request $request)
    {

        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            if ($user->role === 'candidate') {
                $user->load(['candidate', 'candidate.document']);
            } elseif ($user->role === 'monitor') {
                $user->load('monitor');
            }


            if ($user->profile_picture) {
                $user->profile_picture_url = url(Storage::url($user->profile_picture));
            }

            return response()->json([
                'user' => $user,
                'role' => $user->role,
                'isAdmin' => $user->role === 'admin',
                'isMonitor' => $user->role === 'monitor',
                'isCandidate' => $user->role === 'candidate',
            ]);
        } catch (\Exception $e) {

            Log::error('Error fetching user data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des données utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
