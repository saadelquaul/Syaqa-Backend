<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10000' ,
            'video' => 'required|file|mimes:mp4|max:102400',
        ];

    }


    public function messages(): array
    {
        return [
            'title.required' => 'The course title is required.',
            'description.required' => 'The course description is required.',
            'category_id.required' => 'The category is required.',
            'image.image' => 'The image must be an image file.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'image.max' => 'The image size must not exceed 10MB.',
            'video.required' => 'The video is required.',
            'video.mimes' => 'The video must be a file of type: mp4.',
            'video.max' => 'The video size must not exceed 100MB.',
        ];
    }
}
