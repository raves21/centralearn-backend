<?php

namespace App\Http\Requests\Chapter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'course_class_id' => ['required', 'exists:course_classes,id'],
            'description' => ['nullable', 'string'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('chapters')->where(fn($q) => $q->where('course_class_id', $this->course_class_id))
            ],
            'published_at' => ['nullable', 'date']
        ];
    }
}
