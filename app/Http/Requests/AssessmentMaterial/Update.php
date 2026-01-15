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
            'material_type' => ['nullable', 'in:option_based_question,essay_question,text,file'],
            'order'         => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('assessment_materials')
                    ->where(fn($q) => $q->where('assessment_id', $assessmentId))
                    ->ignore($this->route('assessment_material'))
            ],
            'material'      => ['nullable'],
        ];

        $materialType = $this->input('material_type');

        if (!$materialType) return $rules;

        $materialType = $this->input('material_type');

        switch ($materialType) {
            case 'text':
                $rules = [
                    ...$rules,
                    'material.content' => ['nullable', 'string'],
                ];
                break;

            case 'file':
                $rules = [
                    ...$rules,
                    'material.file' => [
                        'nullable',
                        'file',
                        'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                        'max:307200',
                    ],
                ];
                break;

            case 'option_based_question':
                $rules = [
                    ...$rules,
                    'material.option_based_question' => ['nullable'],
                    'material.option_based_question.question_text' => [
                        'nullable',
                        'string'
                    ],
                    'material.option_based_question.point_worth' => [
                        'nullable',
                        'integer',
                        'min:0'
                    ]
                ];
                break;

            case 'essay_question':
                $rules = [
                    ...$rules,
                    'material.essay_question' => ['nullable'],
                    'material.essay_question.question_text' => [
                        'nullable',
                        'string'
                    ],
                    'material.essay_question.point_worth' => [
                        'nullable',
                        'integer',
                        'min:0'
                    ],
                ];
                break;
        }


        return $rules;
    }
}
