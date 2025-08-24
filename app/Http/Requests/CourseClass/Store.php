<?php

namespace App\Http\Requests\CourseClass;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
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
            'course_id' => ['required', 'string'],
            'semester_id' => ['required', 'string'],
            'section_name' => ['required', 'string'],
            'status' => ['required', 'in:open,close'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:10000'],
        ];
    }
}
