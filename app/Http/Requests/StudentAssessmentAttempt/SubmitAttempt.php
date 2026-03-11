<?php

namespace App\Http\Requests\StudentAssessmentAttempt;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAttempt extends FormRequest
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
            'attempt_id' => ['required', 'exists:student_assessment_attempts,id'],
            'answers' => ['nullable', 'array'],
            'answers.*.asmt_material_id' => ['required', 'exists:assessment_materials,id'],
            'answers.*.material_type' => ['required', 'in:essay_item,option_based_item,identification_item'],
            'answers.*.content' => ['nullable', 'string']
        ];
    }
}
