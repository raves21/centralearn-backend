<?php

namespace App\Http\Requests\Chapter;

use App\Models\Chapter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $courseClassId = Chapter::find($this->route('chapter'))->course_class_id;

        return [
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'order' => [
                'nullable',
                'integer',
                Rule::unique('chapters')
                    ->where(fn($q) => $q->where('course_class_id', $courseClassId))
                    ->ignore($this->route('chapter'))
            ],
            'published_at' => ['nullable', 'date']
        ];
    }
}
