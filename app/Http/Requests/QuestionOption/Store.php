<?php

namespace App\Http\Requests\QuestionOption;

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
            'option_based_question_id' => ['required', 'exists:option_based_questions,id'],
            'option_type' => ['required', 'in:text,file'],
            'is_correct' => ['required', 'boolean'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('question_options')
                    ->where(fn($q) => $q->where('option_based_question_id', $this->option_based_question_id))
                    ->ignore($this->route('question_option'))
            ],
            'option' => ['required'],
        ];

        $optionType = $this->input('option_type');

        switch ($optionType) {
            case 'text':
                $rules = [
                    ...$rules,
                    'option.content' => ['required', 'string'],
                ];
                break;

            case 'file':
                $rules = [
                    ...$rules,
                    'option.file' => [
                        'required',
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
