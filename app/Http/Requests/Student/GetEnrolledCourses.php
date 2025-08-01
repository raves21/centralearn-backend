<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class GetEnrolledCourses extends FormRequest
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
            'semester_id' => ['sometimes', 'exists:semesters,id'],
            'course_name' => ['sometimes', 'string']
        ];
    }
}
