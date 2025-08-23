<?php

namespace App\Http\Requests\LectureMaterial;

use App\Models\LectureMaterial;
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
        $lectureId = LectureMaterial::find($this->route('lecture_material'))->lecture_id;
        $rules = [
            //general info
            'material_type' => ['required', 'in:text,file'],
            'order' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('lecture_materials')
                    ->where(fn($q) => $q->where('lecture_id', $lectureId))
                    ->ignore($this->route('lecture_material')),
            ],
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
