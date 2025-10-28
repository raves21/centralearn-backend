<?php

namespace App\Http\Requests\Course;

use App\Rules\FileOrDeleted;
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
            'code' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', new FileOrDeleted()],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['exists:departments,id']
        ];
    }
}
