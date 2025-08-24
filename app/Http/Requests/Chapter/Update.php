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
        $courseClassId = Chapter::find($this->route('chapter'))->course_semester_id;

        return [
            'name' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'order' => [
                'sometimes',
                'integer',
                Rule::unique('course_semesters')
                    ->where(fn($q) => $q->where('course_semester_id', $courseClassId))
                    ->ignore($this->route('chapter'))
            ],
            'published_at' => ['sometimes', 'date']
        ];
    }
}
