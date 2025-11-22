<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class GetEnrolledClasses extends FormRequest
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
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'status' => ['nullable', 'in:open,close'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'query' => ['nullable', 'string'],
            'paginate' => ['nullable', 'boolean']
        ];
    }
    protected function prepareForValidation()
    {
        if ($this->has('paginate')) {
            $this->merge([
                'paginate' => filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
