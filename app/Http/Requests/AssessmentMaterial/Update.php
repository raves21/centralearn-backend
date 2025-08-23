<?php

namespace App\Http\Requests\AssessmentMaterial;

use App\Models\AssessmentMaterial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
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
        $assessmentId = AssessmentMaterial::find($this->route('assessment_material'))->assessment_id;
        $rules = [
            // general info
            'is_material_updated' => ['required', 'boolean'],
            'material_type' => ['sometimes', 'in:option_based_question,text_based_question,text,file'],
            'order'         => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('assessment_materials')
                    ->where(fn($q) => $q->where('assessment_id', $assessmentId))
                    ->ignore($this->route('assessment_material'))
            ],
            'material'      => ['sometimes'],
        ];

        $materialType = $this->input('material_type');

        if (!$materialType) return $rules;

        $materialType = $this->input('material_type');

        switch ($materialType) {
            case 'text':
                $rules = [
                    ...$rules,
                    'material.content' => ['sometimes', 'string'],
                ];
                break;

            case 'file':
                $rules = [
                    ...$rules,
                    'material.file' => [
                        'sometimes',
                        'file',
                        'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                        'max:307200',
                    ],
                ];
                break;

            case 'option_based_question':
                $rules = [
                    ...$rules,
                    'material.option_based_question' => ['sometimes'],
                    'material.option_based_question.question_text' => [
                        'sometimes',
                        'string'
                    ],
                    'material.option_based_question.point_worth' => [
                        'sometimes',
                        'integer',
                        'min:0'
                    ]
                ];
                break;

            case 'text_based_question':
                $rules = [
                    ...$rules,
                    'material.text_based_question' => ['sometimes'],
                    'material.text_based_question.question_text' => [
                        'sometimes',
                        'string'
                    ],
                    'material.text_based_question.type' => [
                        'sometimes',
                        'in:essay,identification'
                    ],
                    'material.text_based_question.identification_answer' => [
                        'required_if:material.text_based_question.type,identification',
                        'string'
                    ],
                    'material.text_based_question.is_identification_answer_case_sensitive' => [
                        'required_if:material.text_based_question.type,identification',
                        'boolean'
                    ],
                    'material.text_based_question.point_worth' => [
                        'sometimes',
                        'integer',
                        'min:0'
                    ],
                ];
                break;
        }


        return $rules;
    }
}
