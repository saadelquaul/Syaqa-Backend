<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){

        $fields = $request->validate([
            'name' => 'string',
            'email' => 'string|unique:users,email',
            'password' => 'string|confirmed',
            'date_of_birth' => 'date',
            'address' => 'string',
            'phone' => 'string',
            'license_type' => 'string',
            'enrollment_date' => 'date',
            'CIN' => 'file|mimes:pdf,jpg,png,jpeg|max:2048',
            'CM' => 'file|mimes:pdf,jpg,png,jpeg|max:2048',
        ]);

        DB::beginTransaction();

        try
        {

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        $candidate = $user->candidate()->create([
                'date_of_birth' => $fields['date_of_birth'],
                'address' => $fields['address'],
                'phone_number' => $fields['phone'],
                'license_type' => $fields['license_type'],
                'enrollment_date' => $fields['enrollment_date'],
            ]);

            $cinFile = $request->file('CIN');
            $cmFile = $request->file('CM');


            Document::create([
                    'candidate_id' => $candidate->id,
                    'CIN' => $cinFile->store('documents'),
                    'cin_type' => $cinFile->extension(),
                    'CM' => $cmFile->store('documents'),
                    'cm_type' => $cmFile->extension(),
                ]);

            DB::commit();
                return response()->json([
                    'message' => 'Compte candidat créé avec succès',
                    'user' => $user,
                    'condidate' => $candidate,
                    ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            return response([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500);
        }
}

    public function monitorRegister(Request $request){

        $fields = $request->validate([
            'name' => 'string',
            'email' => 'string|unique:users,email',
            'password' => 'string|confirmed',
            'date_of_birth' => 'date',
            'address' => 'string',
            'phone' => 'string',
            'license_number' => 'string',
            'employment_date' => 'date',
            'profile_picture' => 'file|mimes:jpg,png,jpeg|max:2048',
        ]);

        DB::beginTransaction();

        try
        {

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => 'monitor',
        ]);

        $monitor = $user->monitor()->create([
                'date_of_birth' => $fields['date_of_birth'],
                'address' => $fields['address'],
                'phone_number' => $fields['phone'],
                'license_number' => $fields['license_number'],
                'employment_date' => $fields['employment_date'],
                'profile_picture' => $fields['profile_picture']->store('profile_pictures'),
            ]);

            DB::commit();
                return response()->json([
                    'message' => 'Compte moniteur créé avec succès',
                    'user' => $user,
                    'monitor' => $monitor,
                    ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            return response([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);


        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        if($user->role == 'candidate' && $user->candidate->status == 'inactive') {
            return response()->json([
                'message' => 'Votre compte est inactif sill vous plait attendez la validation de votre compte'
            ], 401);
        }

        $token = $user->createToken('Syaqa');
        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken
        ], 201);

    }

    public function logout(Request $request) {

        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnecté'
        ]);
    }
}
