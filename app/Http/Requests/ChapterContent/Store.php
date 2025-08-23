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
            'is_published' => ['required', 'boolean'],
            'publishes_at' => ['nullable', 'date'],

            //accessibility
            'is_open' => ['required', 'boolean'],
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
                'content.is_multi_attempts' => ['sometimes', 'boolean'],
                'content.max_attempts' => ['nullable', 'required_if:content.is_multi_attempts,true', 'integer'],
                'content.multi_attempt_grading_type' => ['nullable', 'required_if:content.is_multi_attempts,true', 'in:avg_score,highest_score'],
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

            //publishes_at must only be set if not published
            if ($isPublished && $publishesAt !== null) {
                $validator->errors()->add('publishes_at', 'publishes_at can only be set if the content is not yet published.');
            }

            //opens_at must only be set if is_open is false
            if ($isOpen && $opensAt !== null) {
                $validator->errors()->add('opens_at', 'opens_at can only be set if the content is not already open.');
            }

            //closes_at must only be set if is_open is true or opens_at has value
            if ((!$isOpen || $opensAt === null) && $closesAt !== null) {
                $validator->errors()->add('closes_at', 'closes_at must only be set if content is already open, or if opens_at has value.');
            }
        });
    }
}
