<?php

namespace App\Http\Requests\CourseClass;

use App\Rules\FileOrDeleted;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'course_id' => ['required', 'exists:courses,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'section_id' => [
                'required',
                'exists:sections,id',
                Rule::unique('course_classes')
                    ->where(fn($q) => $q
                        ->where('course_id', $this->course_id)
                        ->where('semester_id', $this->semester_id))
            ],
            'status' => ['required', 'in:open,close'],
            'image' => ['nullable', new FileOrDeleted()],
        ];
    }

    public function messages()
    {
        return [
            'section_id.unique' => 'A Class with this Course, Semester, and Section already exists.'
        ];
    }
}
