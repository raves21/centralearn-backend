<?php

namespace App\Http\Requests\AssessmentMaterial;

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
        $rules = [
            // general info
            'assessment_id'    => ['required', 'exists:assessments,id'],
            'material_type' => ['required', 'in:option_based_question,essay_question,text,file'],
            'order'         => [
                'required',
                'integer',
                'min:1',
                Rule::unique('assessment_materials')->where(fn($q) => $q->where('assessment_id', $this->assessment_id))
            ],
            'material'      => ['required'],
        ];

        $materialType = $this->input('material_type');

        switch ($materialType) {
            case 'text':
                $rules = [
                    ...$rules,
                    'material.content' => ['required', 'string'],
                ];
                break;

            case 'file':
                $rules = [
                    ...$rules,
                    'material.file' => [
                        'required',
                        'file',
                        'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                        'max:307200',
                    ],
                ];
                break;

            case 'option_based_question':
                $rules = [
                    ...$rules,
                    'material.option_based_question' => ['required'],
                    'material.option_based_question.question_text' => [
                        'required',
                        'string'
                    ],
                    'material.option_based_question.point_worth' => [
                        'required',
                        'integer',
                        'min:0'
                    ]
                ];
                break;

            case 'essay_question':
                $rules = [
                    ...$rules,
                    'material.essay_question' => ['required'],
                    'material.essay_question.question_text' => [
                        'required',
                        'string'
                    ],
                    'material.essay_question.point_worth' => [
                        'required',
                        'integer',
                        'min:0'
                    ],
                ];
                break;
        }


        return $rules;
    }
}
