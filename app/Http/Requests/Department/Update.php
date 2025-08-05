<?php

namespace App\Http\Requests\Department;

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
            'name' => ['sometimes', 'string'],
            'code' => ['sometimes', 'string'],
            'image' => ['sometimes', 'file', 'mimes:jpeg,jpg,png,webp', 'max:15000'],
            'description' => ['sometimes', 'string']
        ];
    }
}
