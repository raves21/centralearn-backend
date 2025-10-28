<?php

namespace App\Http\Requests\Program;

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
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:10000'],
            'code' => ['nullable', 'string']
        ];
    }
}
