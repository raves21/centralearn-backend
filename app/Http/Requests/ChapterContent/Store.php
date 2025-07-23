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
        return [
            'chapter_id' => ['required', 'exists:course_chapters,id'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer'],
            'is_published' => ['required', 'boolean'],
            'publishes_at' => ['nullable', 'date'],
            'content_type' => ['required', 'in:lecture,assessment'],
            'content' => ['required_if:content_type,assessment'],

            'content.is_open' => ['required', 'boolean'],
            'content.opens_at' => ['missing_if:content.is_open,true', 'nullable', Rule::date()->afterOrEqual(today())],
            'content.closes_at' => ['nullable', 'date', 'after:content.opens_at'],
            'content.time_limit' => ['nullable', 'integer'],
            'content.max_score' => ['nullable', 'numeric'],
            'content.is_answers_viewable_after_submit' => ['required', 'boolean'],
            'content.is_score_viewable_after_submit' => ['required', 'boolean'],
            'content.is_multi_attempts' => ['required', 'boolean'],
            'content.max_attempts' => ['required_if:content.is_multi_attempts,true', 'integer'],
            'content.multi_attempt_grading_type' => ['required_if:content.is_multi_attempts,true', 'in:avg_score,highest_score']
        ];
    }
}
