<?php

namespace App\Http\Requests\ChapterContent;

use App\Models\Assessment;
use App\Models\ChapterContent;
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
        // Get the model ID from the route. Key depends on route name (usually 'chapter_content')
        $chapterContentId = $this->route('chapter_content');
        $chapterContent = ChapterContent::find($chapterContentId);

        // chapter_id used for uniqueness checks (provided in input or fallback to existing)
        $chapterId = $this->input('chapter_id');

        $rules = [
            // General info
            'chapter_id' => ['required', 'exists:chapters,id'],
            'name' => ['sometimes', 'string'],
            'description' => ['nullable', 'string'],
            'order' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('chapter_contents')
                    ->where(fn($q) => $q->where('chapter_id', $chapterId))
                    ->ignore($chapterContentId)
            ],

            'content_type' => ['sometimes', 'in:lecture,assessment'],
            'content' => ['sometimes', 'array'],

            // Accessibility settings
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

        // Determine content type
        $contentType = $this->input('content_type');
        if (!$contentType && $chapterContent) {
            $contentType = $chapterContent->contentable_type === Assessment::class ? 'assessment' : 'lecture';
        }

        // Apply assessment rules if content_type is assessment
        if ($contentType === 'assessment') {
            $rules = [
                ...$rules,
                'content.max_achievable_score' => ['nullable', 'numeric', 'min:0'],
                'content.is_answers_viewable_after_submit' => ['sometimes', 'boolean'],
                'content.is_score_viewable_after_submit' => ['sometimes', 'boolean'],
                'content.max_attempts' => ['sometimes', 'integer', 'min:1'],
                'content.multi_attempt_grading_type' => [
                    'nullable',
                    Rule::requiredIf(function () use ($chapterContent) {
                        $maxAttempts = data_get($this->input(), 'content.max_attempts');
                        // Use DB fallback for max_attempts if not in current input
                        if (is_null($maxAttempts) && $chapterContent && $chapterContent->contentable_type === Assessment::class) {
                            $maxAttempts = $chapterContent->contentable->max_attempts;
                        }
                        return (int) $maxAttempts > 1;
                    }),
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
