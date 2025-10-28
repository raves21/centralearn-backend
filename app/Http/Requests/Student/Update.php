<?php

namespace App\Http\Requests\Student;

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
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            //can only update program_id if not enrolled to any courseClass
            'program_id' => ['nullable', 'exists:programs,id'],
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
