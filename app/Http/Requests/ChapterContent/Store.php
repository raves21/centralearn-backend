<?php

namespace App\Http\Requests\ChapterContent;

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
        $rules = [

            //general info
            'chapter_id' => ['required', 'exists:chapters,id'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('chapter_contents')->where(fn($q) => $q->where('chapter_id', $this->chapter_id))
            ],
            'content_type' => ['required', 'in:lecture,assessment'],
            'content' => ['required_if:content_type,assessment'],

            //visibility
            'publishes_at' => ['nullable', 'date'],

            //accessibility
            'opens_at' => ['nullable', 'date', Rule::date()->afterOrEqual(today())],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
        ];

        // Only apply assessment rules if content_type is assessment
        if ($this->input('content_type') === 'assessment') {
            $rules = [
                ...$rules,
                'content.time_limit' => ['nullable', 'integer'],
                'content.max_score' => ['nullable', 'numeric'],
                'content.is_answers_viewable_after_submit' => ['required', 'boolean'],
                'content.is_score_viewable_after_submit' => ['required', 'boolean'],
                'content.is_multi_attempts' => ['nullable', 'boolean'],
                'content.max_attempts' => ['nullable', 'required_if:content.is_multi_attempts,true', 'integer'],
                'content.multi_attempt_grading_type' => ['nullable', 'required_if:content.is_multi_attempts,true', 'in:avg_score,highest_score'],
            ];
        }

        return $rules;
    }
}
