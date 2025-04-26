<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authorization for now
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('post') && $this->routeIs('register')) {

            return [
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|confirmed',
                'date_of_birth' => 'required|date',
                'address' => 'required|string',
                'phone' => 'required|string',
                'license_type' => 'required|string',
                'enrollment_date' => 'required|date',
                'CIN' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
                'profile_picture' => 'string',
            ];
        }

        if ($this->isMethod('post') && $this->routeIs('register-monitor')) {

            return [
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|confirmed',
                'date_of_birth' => 'required|date',
                'address' => 'required|string',
                'phone' => 'required|string',
                'license_number' => 'required|string',
                'employment_date' => 'required|date',
                'profile_picture' => 'required|file|mimes:jpg,png,jpeg|max:2048',
            ];
        }

        return [];
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'email est requis.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'date_of_birth.required' => 'La date de naissance est requise.',
            'address.required' => 'L\'adresse est requise.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'license_type.required' => 'Le type de permis est requis.',
            'enrollment_date.required' => 'La date d\'inscription est requise.',
            'CIN.required' => 'Le fichier CIN est requis.',
            'CIN.mimes' => 'Le fichier CIN doit être un fichier de type : jpg, png, jpeg.',
            'CIN.max' => 'La taille du fichier CIN ne doit pas dépasser 2 Mo.',
        ];
    }
}
