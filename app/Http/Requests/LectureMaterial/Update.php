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
        return [
            'lecture_id' => ['required', 'exists:lectures,id'],
            'material_type' => ['required', 'in:text,file'],
            'order' => ['required', 'integer'],
            'material' => ['required'],

            'material.content' => ['required_if:material_type,text'],
            'material.name' => ['required_if:material_type,file', 'string'],
            'material.file' => ['required_if:material_type,file', 'file', 'mimes:pdf,doc,docx,xslx,mkv,mp4,jpg,jpeg,png'],
            'material.type' => ['required_if:material_type,file', 'in:video,document,image']
        ];
    }
}
