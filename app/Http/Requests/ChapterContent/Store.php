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

            //accessibility settings
            'accessibility_settings' => ['nullable', 'array'],
            'accessibility_settings.visible' => [
                'nullable',
                'boolean',
                Rule::prohibitedIf(fn() => !is_null(data_get($this->input(), 'accessibility_settings.custom'))),
            ],
            'accessibility_settings.custom' => [
                'nullable',
                'array',
                Rule::prohibitedIf(fn() => !is_null(data_get($this->input(), 'accessibility_settings.visible'))),
            ],
            'accessibility_settings.custom.access_from' => [
                Rule::requiredIf(fn() => !is_null(data_get($this->input(), 'accessibility_settings.custom'))),
                'date',
            ],
            'accessibility_settings.custom.access_until' => [
                'nullable',
                'date',
                'after:accessibility_settings.custom.access_from',
            ],
        ];

        // Only apply assessment rules if content_type is assessment
        if ($this->input('content_type') === 'assessment') {
            $rules = [
                ...$rules,
                'content.max_achievable_score' => ['nullable', 'numeric', 'min:0'],
                'content.is_answers_viewable_after_submit' => ['required', 'boolean'],
                'content.is_score_viewable_after_submit' => ['required', 'boolean'],
                'content.max_attempts' => ['required', 'integer', 'min:1'],
                'content.multi_attempt_grading_type' => [
                    'nullable',
                    Rule::requiredIf(fn() => (int) data_get($this->input(), 'content.max_attempts') > 1),
                    'in:avg_score,highest_score'
                ],

                // submission settings
                'content.submission_settings' => ['nullable', 'array'],
                'content.submission_settings.time_limit_seconds' => ['nullable', 'integer', 'min:1'],
                'content.submission_settings.due_date' => ['nullable', 'date'],
                'content.submission_settings.after_due_date_behavior' => [
                    'nullable',
                    Rule::requiredIf(fn() => !is_null(data_get($this->input(), 'content.submission_settings.due_date'))),
                    Rule::prohibitedIf(fn() => is_null(data_get($this->input(), 'content.submission_settings.due_date'))),
                    'in:auto_submit,block_new_attempts,allow_all',
                ],
            ];
        }

        return $rules;
    }
}
