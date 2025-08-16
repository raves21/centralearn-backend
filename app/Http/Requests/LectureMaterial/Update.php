<?php

namespace App\Http\Requests\LectureMaterial;

use Illuminate\Foundation\Http\FormRequest;

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
        $rules = [
            //general info
            'material_type' => ['required', 'in:text,file'],
            'order' => ['sometimes', 'integer'],
            'is_material_updated' => ['required', 'boolean'],
            'material' => ['sometimes'],
        ];
        if ($this->input('material_type') === 'text') {
            $rules = [
                ...$rules,
                'material.content' => ['sometimes', 'string']
            ];
        } else {
            $rules = [
                ...$rules,
                'material.file' => [
                    'sometimes',
                    'file',
                    'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                    'max:307200'
                ],
            ];
        }

        return $rules;
    }
}
