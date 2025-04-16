<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:60|unique:categories',
            'description' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
        ];

    }

    public function messages(): array
    {
        return [
            'name.required' => 'le nom de la catégorie est requis.',
            'name.unique' => 'le nom de la catégorie doit être unique.',
            'name.string' => 'le nom de la catégorie doit être une chaîne de caractères.',
            'name.max' => 'le nom de la catégorie ne doit pas dépasser 60 caractères.',
            'description.required' => 'la description de la catégorie est requise.',
            'description.max' => 'la description de la catégorie ne doit pas dépasser 255 caractères.',
            'image.image' => 'The image must be an image file.',
            'image.image' => 'l\'image doit être un fichier image.',
            'image.mimes' => 'l\'image doit être un fichier de type : jpeg, png, jpg.',
            'image.max' => 'la taille de l\'image ne doit pas dépasser 5 Mo.',
        ];
    }
}
