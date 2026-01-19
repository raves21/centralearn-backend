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

            // New materials
            'new' => ['nullable', 'array'],
            'new.*.material_type' => ['required', 'in:essay_item,identification_item,option_based_item'],
            'new.*.order' => ['required', 'integer', 'min:1'],
            'new.*.point_worth' => ['required', 'numeric', 'min:0'],

            // New: Question validation
            'new.*.question' => ['required', 'array'],
            'new.*.question.question_text' => ['required', 'string'],
            'new.*.question.question_files' => ['nullable', 'array'],
            'new.*.question.question_files.*' => [
                'file',
                'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                'max:51200'
            ],

            // New: Type specific
            'new.*.essay_item' => ['required_if:new.*.material_type,essay_item', 'array'],
            'new.*.essay_item.min_character_count' => ['nullable', 'integer', 'min:0'],
            'new.*.essay_item.max_character_count' => ['nullable', 'integer', 'min:0'],
            'new.*.essay_item.min_word_count' => ['nullable', 'integer', 'min:0'],
            'new.*.essay_item.max_word_count' => ['nullable', 'integer', 'min:0'],

            'new.*.identification_item' => ['required_if:new.*.material_type,identification_item', 'array'],
            'new.*.identification_item.accepted_answers' => ['required_if:new.*.material_type,identification_item', 'array', 'min:1'],
            'new.*.identification_item.accepted_answers.*' => ['string'],

            'new.*.option_based_item' => ['required_if:new.*.material_type,option_based_item', 'array'],
            'new.*.option_based_item.is_multiple_choice' => ['nullable', 'boolean'],
            'new.*.option_based_item.options' => ['required_if:new.*.material_type,option_based_item', 'array', 'min:2'],
            'new.*.option_based_item.options.*.is_correct' => ['nullable', 'boolean'],

            // New: Option structure update (text or file, strictly one required if other is null)
            'new.*.option_based_item.options.*.option_text' => ['nullable', 'string', 'required_without:new.*.option_based_item.options.*.option_file'],
            'new.*.option_based_item.options.*.option_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png', 'max:51200', 'required_without:new.*.option_based_item.options.*.option_text'],

            // Updated materials
            'updated' => ['nullable', 'array'],
            'updated.*.id' => ['required', 'exists:assessment_materials,id'],
            'updated.*.material_type' => ['required', 'in:essay_item,identification_item,option_based_item'],
            'updated.*.order' => ['nullable', 'integer', 'min:1'],
            'updated.*.point_worth' => ['nullable', 'numeric', 'min:0'],
            'updated.*.is_material_updated' => ['required', 'boolean'],

            // Updated: Question (optional update)
            'updated.*.question' => ['nullable', 'array'],
            'updated.*.question.question_text' => ['nullable', 'string'],
            'updated.*.question.question_files' => ['nullable', 'array'],
            'updated.*.question.question_files.*' => [
                'file',
                'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                'max:51200'
            ],

            // Updated: Essay Item (optional update)
            'updated.*.essay_item' => ['nullable', 'array'],
            'updated.*.essay_item.min_character_count' => ['nullable', 'integer', 'min:0'],
            'updated.*.essay_item.max_character_count' => ['nullable', 'integer', 'min:0'],
            'updated.*.essay_item.min_word_count' => ['nullable', 'integer', 'min:0'],
            'updated.*.essay_item.max_word_count' => ['nullable', 'integer', 'min:0'],

            // Updated: Identification Item (optional update)
            'updated.*.identification_item' => ['nullable', 'array'],
            'updated.*.identification_item.accepted_answers' => ['nullable', 'array', 'min:1'],
            'updated.*.identification_item.accepted_answers.*' => ['string'],

            // Updated: Option Based Item (optional update)
            'updated.*.option_based_item' => ['nullable', 'array'],
            'updated.*.option_based_item.is_multiple_choice' => ['nullable', 'boolean'],
            'updated.*.option_based_item.options' => ['nullable', 'array', 'min:2'],
            'updated.*.option_based_item.options.*.id' => ['nullable', 'exists:option_based_item_options,id'],
            'updated.*.option_based_item.options.*.is_correct' => ['nullable', 'boolean'],

            // Updated: Option structure update
            // For updates, we can't strict check 'required_without' easily because file might be pre-existing.
            // But if it's a NEW option in the updated list (no ID), rules should apply.
            // For existing options, we trust the client sends what they want to change.
            'updated.*.option_based_item.options.*.option_text' => ['nullable', 'string'],
            'updated.*.option_based_item.options.*.option_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:51200'],

            // Deleted materials
            'deleted' => ['nullable', 'array'],
            'deleted.*' => ['required', 'exists:assessment_materials,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $assessmentId = $this->input('assessment_id');
            $new = $this->input('new', []);
            $updated = $this->input('updated', []);

            if (!$assessmentId) {
                return;
            }

            // 1. Validate NEW materials orders
            $newOrders = [];
            foreach ($new as $index => $material) {
                $order = $material['order'] ?? null;
                if ($order) {
                    if (in_array($order, $newOrders)) {
                        $validator->errors()->add(
                            "new.{$index}.order",
                            "The order value {$order} is duplicated in this request."
                        );
                    }
                    $newOrders[] = $order;
                }
            }

            $existingOrders = [];
            if (!empty($newOrders)) {
                $existingOrders = DB::table('assessment_materials')
                    ->where('assessment_id', $assessmentId)
                    ->whereIn('order', $newOrders)
                    ->pluck('order')
                    ->toArray();

                foreach ($new as $index => $material) {
                    if (isset($material['order']) && in_array($material['order'], $existingOrders)) {
                        $validator->errors()->add(
                            "new.{$index}.order",
                            "The order {$material['order']} already exists for this assessment."
                        );
                    }
                }
            }

            // 2. Validate UPDATED materials
            $updatedIds = array_column($updated, 'id');

            foreach ($updated as $index => $material) {
                // Check options logic for Option Based Items
                if (
                    isset($material['material_type']) &&
                    $material['material_type'] === 'option_based_item' &&
                    isset($material['option_based_item']['options'])
                ) {
                    foreach ($material['option_based_item']['options'] as $optIndex => $option) {
                        $optId = $option['id'] ?? null;
                        $optText = $option['option_text'] ?? null;
                        $optFile = $option['option_file'] ?? null;

                        // If it's a NEW option (no ID)
                        if (!$optId) {
                            if (!$optText && !$optFile) {
                                $validator->errors()->add(
                                    "updated.{$index}.option_based_item.options.{$optIndex}",
                                    "Either option text or option file is required for new options."
                                );
                            }
                        }
                    }
                }

                // Check order Uniqueness
                if (!isset($material['id']) || !isset($material['order'])) {
                    continue;
                }

                $order = $material['order'];

                // Ensure order is not taken by another material NOT in this update batch
                $orderExists = DB::table('assessment_materials')
                    ->where('assessment_id', $assessmentId)
                    ->where('order', $order)
                    ->whereNotIn('id', $updatedIds) // Ignore items being updated in this batch
                    ->exists();

                if ($orderExists) {
                    $validator->errors()->add(
                        "updated.{$index}.order",
                        "The order {$order} is already taken by another material."
                    );
                }
            }

            // 3. Check for duplicates within 'updated' batch
            $updatedOrders = [];
            foreach ($updated as $index => $material) {
                $order = $material['order'] ?? null;
                if ($order) {
                    if (in_array($order, $updatedOrders)) {
                        $validator->errors()->add(
                            "updated.{$index}.order",
                            "The order {$order} is duplicated in the updated items."
                        );
                    }
                    $updatedOrders[] = $order;
                }
            }

            // 4. Check collisions between 'new' and 'updated'
            // New orders vs Updated orders
            $intersection = array_intersect($newOrders, $updatedOrders);
            if (!empty($intersection)) {
                foreach ($intersection as $dupOrder) {
                    $validator->errors()->add(
                        "operations",
                        "Order {$dupOrder} is assigned to both new and updated items."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            // New
            'new.*.material_type.in' => 'Material type must be one of: essay_item, identification_item, option_based_item.',
            'new.*.question.question_text.required' => 'Question text is required.',
            'new.*.essay_item.required_if' => 'Essay item details are required.',
            'new.*.identification_item.required_if' => 'Identification item details are required.',
            'new.*.identification_item.accepted_answers.required_if' => 'Accepted answers are required.',
            'new.*.option_based_item.required_if' => 'Option details are required.',
            'new.*.option_based_item.options.required_if' => 'Options are required.',
            'new.*.option_based_item.options.min' => 'At least 2 options are required.',

            'new.*.option_based_item.options.*.option_text.required_without' => 'Option text is required if no file is uploaded.',
            'new.*.option_based_item.options.*.option_file.required_without' => 'Option file is required if no text is provided.',

            // Updated
            'updated.*.id.required' => 'Material ID is required for updates.',
            'updated.*.id.exists' => 'The selected material does not exist.',
            'updated.*.option_based_item.options.min' => 'At least 2 options are required for option based items.',
        ];
    }
}
