<?php

namespace App\Http\Requests\LectureMaterial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Store extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // general info
            'lecture_id'    => ['required', 'exists:lectures,id'],
            'material_type' => ['required', 'in:text,file'],
            'order'         => [
                'required',
                'integer',
                'min:1',
                Rule::unique('lecture_materials')->where(fn($q) => $q->where('lecture_id', $this->lecture_id))
            ],
        ];

        if ($this->input('material_type') === 'text') {
            $rules = [
                ...$rules,
                'material_content' => ['required_if:material_type,text', 'string'],
            ];
        } else {
            $rules = [
                ...$rules,
                'material_file' => [
                    'required_if:material_type,file',
                    'file',
                    'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                    'max:307200',
                ],
            ];
        }

        return $rules;
    }
}
