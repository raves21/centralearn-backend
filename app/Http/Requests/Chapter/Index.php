<?php

namespace App\Http\Requests\Chapter;

use Illuminate\Foundation\Http\FormRequest;

class Index extends FormRequest
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
            'course_class_id' => ['required', 'exists:course_classes,id'],
            'include_chapter_contents' => ['nullable', 'boolean']
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('include_chapter_contents')) {
            $this->merge([
                'include_chapter_contents' => filter_var($this->include_chapter_contents, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
