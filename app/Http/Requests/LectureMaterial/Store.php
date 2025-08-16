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
            'material'      => ['required'],
        ];

        if ($this->input('material_type') === 'text') {
            $rules = [
                ...$rules,
                'material.content' => ['required', 'string'],
            ];
        } else {
            $rules = [
                ...$rules,
                'material.file' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                    'max:307200',
                ],
            ];
        }

        return $rules;
    }
}
