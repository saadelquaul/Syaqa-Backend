<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

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
}
