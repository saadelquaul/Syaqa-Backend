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
        $rules =  [
            'description' => 'string',
            'category_id' => 'exists:categories,id',
        ];

        if ($this->isMethod('post')) {
            $rules['title'] = 'required|string|max:255';
            $rules['description'] = 'required|string';
            $rules['category_id'] = 'required|integer|exists:categories,id';
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg|max:10000';
            $rules['video'] = 'required|file|mimes:mp4|max:102400';
        } else if( $this->isMethod('put')) {
            $rules['title'] = 'sometimes|string|max:255';
            $rules['description'] = 'sometimes|string';
            $rules['category_id'] = 'sometimes|integer|exists:categories,id';
            $rules['duration'] = 'sometimes|string|max:50';
            $rules['image'] = 'sometimes|image|mimes:jpeg,png,jpg|max:10000';
            $rules['video'] = 'sometimes|file|mimes:mp4|max:102400';
        }

        return $rules;
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
