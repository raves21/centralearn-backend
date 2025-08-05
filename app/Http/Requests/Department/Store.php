<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => ['required', 'string'],
            'code' => ['required', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:15000'],
            'description' => ['nullable', 'string']
        ];
    }
}
