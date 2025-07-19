<?php

namespace App\Http\Requests\CourseChapterController;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
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
            'name' => ['required', 'string'],
            'course_id' => ['required', 'exists:courses,id'],
            'description' => ['sometimes', 'string'],
            'order' => ['required', 'integer'],

        ];
    }
}
