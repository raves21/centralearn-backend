<?php

namespace App\Http\Requests\AssessmentResult;

use Illuminate\Foundation\Http\FormRequest;

class GetResultAndAttempts extends FormRequest
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
            'student_id' => ['required', 'exists:students,id'],
            'assessment_id' => ['required', 'exists:assessments,id']
        ];
    }
}
