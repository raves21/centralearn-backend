<?php

namespace App\Http\Requests\ChapterContent;

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
            'contents' => ['required', 'array'],
            'contents.*.id' => ['required', 'exists:chapter_contents,id'],
            'contents.*.new_order' => ['required', 'integer', 'min:1']
        ];
    }
}
