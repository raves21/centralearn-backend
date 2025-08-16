<?php

namespace App\Http\Requests\ChapterContent;

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
        $rules = [

            // General info
            'chapter_id' => ['sometimes', 'exists:chapters,id'],
            'name' => ['sometimes', 'string'],
            'description' => ['nullable', 'string'],
            'order' => ['sometimes', 'integer', 'unique:chapter_contents,order'],
            'content_type' => ['sometimes', 'in:lecture,assessment'],
            'content' => ['required_if:content_type,assessment'],

            // Visibility
            'is_published' => ['sometimes', 'boolean:strict'],
            'publishes_at' => ['nullable', 'date'],

            // Accessibility
            'is_open' => ['sometimes', 'boolean:strict'],
            'opens_at' => ['nullable', 'date', Rule::afterOrEqual(today())],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
        ];

        // Only apply assessment rules if content_type is "assessment"
        if ($this->input('content_type') === 'assessment') {
            $rules = array_merge($rules, [
                'content.time_limit' => ['nullable', 'integer'],
                'content.max_score' => ['nullable', 'numeric'],
                'content.is_answers_viewable_after_submit' => ['sometimes', 'boolean:strict'],
                'content.is_score_viewable_after_submit' => ['sometimes', 'boolean:strict'],
                'content.is_multi_attempts' => ['sometimes', 'boolean:strict'],
                'content.max_attempts' => ['required_if:content.is_multi_attempts,true', 'integer'],
                'content.multi_attempt_grading_type' => ['required_if:content.is_multi_attempts,true', 'in:avg_score,highest_score'],
            ]);
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isPublished = $this->boolean('is_published');
            $publishesAt = $this->input('publishes_at');
            $isOpen = $this->boolean('content.is_open');
            $opensAt = $this->input('content.opens_at');
            $closesAt = $this->input('content.closes_at');

            if ($isPublished && $publishesAt !== null) {
                $validator->errors()->add('publishes_at', 'publishes_at can only be set if the content is not yet published.');
            }

            if ($isOpen && $opensAt !== null) {
                $validator->errors()->add('content.opens_at', 'opens_at can only be set if the content is not already open.');
            }

            if ((!$isOpen || $opensAt === null) && $closesAt !== null) {
                $validator->errors()->add('content.closes_at', 'closes_at must only be set if content is already open, or if opens_at has value.');
            }
        });
    }
}
