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
}
