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
            'is_published' => ['nullable', 'boolean'],
            'publishes_at' => ['nullable', 'date'],

            // Accessibility
            'is_open' => ['nullable', 'boolean'],
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isPublished = $this->boolean('is_published');
            $publishesAt = $this->input('publishes_at');
            $isOpen = $this->boolean('is_open');
            $opensAt = $this->input('opens_at');
            $closesAt = $this->input('closes_at');

            if ($isPublished && $publishesAt !== null) {
                $validator->errors()->add('publishes_at', 'publishes_at can only be set if the content is not yet published.');
            }

            if ($isOpen && $opensAt !== null) {
                $validator->errors()->add('opens_at', 'opens_at can only be set if the content is not already open.');
            }

            if ((!$isOpen || $opensAt === null) && $closesAt !== null) {
                $validator->errors()->add('closes_at', 'closes_at must only be set if content is already open, or if opens_at has value.');
            }
        });
    }
}
