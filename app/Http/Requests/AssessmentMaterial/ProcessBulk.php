<?php

namespace App\Http\Requests\AssessmentMaterial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProcessBulk extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assessment_id' => ['required', 'exists:assessments,id'],
            'materials' => ['present', 'array'],

            // Common Material Fields
            'materials.*.id' => ['nullable', 'exists:assessment_materials,id'],
            'materials.*.material_type' => ['required', 'in:essay_item,identification_item,option_based_item'],
            'materials.*.order' => ['required', 'integer', 'min:1'],
            'materials.*.point_worth' => ['required', 'numeric', 'min:0'],

            // Question Fields (Always required for sync)
            'materials.*.question' => ['required', 'array'],
            'materials.*.question.question_text' => ['required', 'string'],

            // Existing files we want to KEEP (Array of URLs/Strings)
            'materials.*.question.kept_file_urls' => ['nullable', 'array'],
            'materials.*.question.kept_file_urls.*' => ['string'],

            // New files we want to UPLOAD (Array of Binary Files)
            'materials.*.question.new_question_files' => ['nullable', 'array'],
            'materials.*.question.new_question_files.*' => [
                'file',
                'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                'max:51200'
            ],

            // Type-Specific: Essay
            'materials.*.essay_item' => ['required_if:materials.*.material_type,essay_item', 'array'],
            'materials.*.essay_item.min_character_count' => ['nullable', 'integer', 'min:0'],
            'materials.*.essay_item.max_character_count' => ['nullable', 'integer', 'min:0'],
            'materials.*.essay_item.min_word_count' => ['nullable', 'integer', 'min:0'],
            'materials.*.essay_item.max_word_count' => ['nullable', 'integer', 'min:0'],

            // Type-Specific: Identification
            'materials.*.identification_item' => ['required_if:materials.*.material_type,identification_item', 'array'],
            'materials.*.identification_item.accepted_answers' => ['required_if:materials.*.material_type,identification_item', 'array', 'min:1'],
            'materials.*.identification_item.accepted_answers.*' => ['string'],

            // Type-Specific: Option Based
            'materials.*.option_based_item' => ['required_if:materials.*.material_type,option_based_item', 'array'],
            'materials.*.option_based_item.is_multiple_choice' => ['nullable', 'boolean'],
            'materials.*.option_based_item.options' => ['required_if:materials.*.material_type,option_based_item', 'array', 'min:2'],

            'materials.*.option_based_item.options.*.id' => ['nullable', 'exists:option_based_item_options,id'],
            'materials.*.option_based_item.options.*.is_correct' => ['nullable', 'boolean'],
            'materials.*.option_based_item.options.*.option_text' => ['nullable', 'string'],

            // Option File: Split strategy for sync
            'materials.*.option_based_item.options.*.kept_option_file_url' => ['nullable', 'string'],
            'materials.*.option_based_item.options.*.new_option_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:51200'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $assessmentId = $this->input('assessment_id');
            $materials = $this->input('materials', []);

            $orders = [];

            foreach ($materials as $index => $material) {
                // 1. Validate ID ownership
                $id = $material['id'] ?? null;
                if ($id) {
                    $exists = DB::table('assessment_materials')
                        ->where('id', $id)
                        ->where('assessment_id', $assessmentId)
                        ->exists();

                    if (!$exists) {
                        $validator->errors()->add(
                            "materials.{$index}.id",
                            "The material ID {$id} does not belong to the specified assessment."
                        );
                    }
                }

                // 2. Validate Order Uniqueness within payload
                $order = $material['order'] ?? null;
                if ($order) {
                    if (in_array($order, $orders)) {
                        $validator->errors()->add(
                            "materials.{$index}.order",
                            "The order {$order} is duplicated in the request."
                        );
                    }
                    $orders[] = $order;
                }

                // 3. Option-Specific Logic (New vs Existing Options)
                if (
                    isset($material['material_type']) &&
                    $material['material_type'] === 'option_based_item' &&
                    isset($material['option_based_item']['options'])
                ) {
                    foreach ($material['option_based_item']['options'] as $optIndex => $option) {
                        $optId = $option['id'] ?? null;
                        $optText = $option['option_text'] ?? null;
                        $keptFileUrl = $option['kept_option_file_url'] ?? null;
                        $newFile = isset($option['new_option_file']); // check if file is present in upload

                        // For NEW options (no ID), require text OR file
                        if (!$optId) {
                            $hasFile = $newFile || !empty($keptFileUrl);
                            if (!$optText && !$hasFile) {
                                $validator->errors()->add(
                                    "materials.{$index}.option_based_item.options.{$optIndex}",
                                    "Either option text or option file is required for new options."
                                );
                            }
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'assessment_id.required' => 'Assessment ID is required.',
            'materials.present' => 'Materials list must be provided.',
            'materials.*.material_type.in' => 'Material type must be one of: essay_item, identification_item, option_based_item.',
            'materials.*.question.question_text.required' => 'Question text is required.',

            'materials.*.essay_item.required_if' => 'Essay item details are required.',
            'materials.*.identification_item.required_if' => 'Identification item details are required.',
            'materials.*.identification_item.accepted_answers.required_if' => 'Accepted answers are required.',
            'materials.*.option_based_item.required_if' => 'Option details are required.',
            'materials.*.option_based_item.options.required_if' => 'Options are required.',
            'materials.*.option_based_item.options.min' => 'At least 2 options are required.',
        ];
    }
}
