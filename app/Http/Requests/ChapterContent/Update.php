<?php

namespace App\Http\Requests\ChapterContent;

use App\Models\ChapterContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $chapterId = ChapterContent::find($this->route('content'))->chapter_id;
        $rules = [
            // General info
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('chapter_contents')
                    ->where(fn($q) => $q->where('chapter_id', $chapterId))
            ],
            'content_type' => ['nullable', 'in:lecture,assessment'],
            'content' => ['required_if:content_type,assessment'],

            // Visibility
            'publishes_at' => ['nullable', 'date'],

            // Accessibility
            'opens_at' => ['nullable', 'date', 'after_or_equal:today'],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
        ];

        // Only apply assessment rules if content_type is "assessment"
        if ($this->input('content_type') === 'assessment') {
            $rules = [
                ...$rules,
                'content.time_limit' => ['nullable', 'integer'],
                'content.max_score' => ['nullable', 'numeric'],
                'content.is_answers_viewable_after_submit' => ['nullable', 'boolean'],
                'content.is_score_viewable_after_submit' => ['nullable', 'boolean'],
                'content.is_multi_attempts' => ['nullable', 'boolean'],
                'content.max_attempts' => ['nullable', 'integer'],
                'content.multi_attempt_grading_type' => ['nullable', 'in:avg_score,highest_score'],
            ];
        }

        return $rules;
    }
}
