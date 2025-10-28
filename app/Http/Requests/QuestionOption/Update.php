<?php

namespace App\Http\Requests\QuestionOption;

use App\Models\QuestionOption;
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
        $optionBasedQId = QuestionOption::find($this->route('question_option'))->option_based_question_id;
        $rules = [
            'is_option_updated' => ['required', 'boolean'],
            'option_type' => ['nullable', 'in:text,file'],
            'is_correct' => ['nullable', 'boolean'],
            'order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('question_options')
                    ->where(fn($q) => $q->where('option_based_question_id', $optionBasedQId))
                    ->ignore($this->route('question_option'))
            ],
            'option' => ['nullable'],
        ];

        $optionType = $this->input('option_type');
        switch ($optionType) {
            case 'text':
                $rules = [
                    ...$rules,
                    'option.content' => ['nullable', 'string'],
                ];
                break;
            case 'file':
                $rules = [
                    ...$rules,
                    'option.file' => [
                        'nullable',
                        'file',
                        'mimes:jpg,jpeg,png',
                        'max:10000',
                    ],
                ];
                break;
        }

        return $rules;
    }
}
