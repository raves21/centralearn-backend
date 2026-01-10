<?php

namespace App\Http\Requests\Chapter;

use Illuminate\Foundation\Http\FormRequest;

class ReorderBulk extends FormRequest
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
            'chapters' => ['required', 'array'],
            'chapters.*.id' => ['required', 'exists:chapters,id'],
            'chapters.*.new_order' => ['required', 'integer', 'min:1']
        ];
    }
}
