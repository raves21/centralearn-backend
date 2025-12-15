<?php

namespace App\Http\Requests\LectureMaterial;

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
            // New materials array validation
            'new' => ['nullable', 'array'],
            'new.*.lecture_id' => ['required', 'exists:lectures,id'],
            'new.*.material_type' => ['required', 'in:text,file'],
            'new.*.order' => [
                'required',
                'integer',
                'min:1',
            ],
            'new.*.material_content' => [
                'required_if:new.*.material_type,text',
                'string',
            ],
            'new.*.material_file' => [
                'required_if:new.*.material_type,file',
                'file',
                'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                'max:307200',
            ],

            // Updated materials array validation
            'updated' => ['nullable', 'array'],
            'updated.*.id' => ['required', 'exists:lecture_materials,id'],
            'updated.*.material_type' => ['required', 'in:text,file'],
            'updated.*.order' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'updated.*.is_material_updated' => ['required', 'boolean'],
            'updated.*.material' => ['nullable'],
            // Note: material.content and material.file validation is handled in withValidator
            // because required_if doesn't work well with nested conditions

            // Deleted materials array validation
            'deleted' => ['nullable', 'array'],
            'deleted.*' => ['required', 'exists:lecture_materials,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate at least one operation is provided
            $new = $this->input('new', []);
            $updated = $this->input('updated', []);
            $deleted = $this->input('deleted', []);

            if (empty($new) && empty($updated) && empty($deleted)) {
                $validator->errors()->add(
                    'operations',
                    'At least one operation (new, updated, or deleted) is required.'
                );
                return;
            }

            // Validate new materials - check for duplicate orders
            $lectureGroups = [];
            foreach ($new as $index => $material) {
                $lectureId = $material['lecture_id'] ?? null;
                $order = $material['order'] ?? null;

                if ($lectureId && $order) {
                    if (!isset($lectureGroups[$lectureId])) {
                        $lectureGroups[$lectureId] = [];
                    }

                    if (in_array($order, $lectureGroups[$lectureId])) {
                        $validator->errors()->add(
                            "new.{$index}.order",
                            "The order value {$order} is duplicated for lecture {$lectureId}."
                        );
                    }

                    $lectureGroups[$lectureId][] = $order;
                }
            }

            // Check for existing orders in database for new materials
            foreach ($lectureGroups as $lectureId => $orders) {
                $existingOrders = DB::table('lecture_materials')
                    ->where('lecture_id', $lectureId)
                    ->whereIn('order', $orders)
                    ->pluck('order')
                    ->toArray();

                if (!empty($existingOrders)) {
                    foreach ($new as $index => $material) {
                        if (
                            isset($material['lecture_id']) &&
                            $material['lecture_id'] === $lectureId &&
                            isset($material['order']) &&
                            in_array($material['order'], $existingOrders)
                        ) {
                            $validator->errors()->add(
                                "new.{$index}.order",
                                "The order {$material['order']} already exists for this lecture."
                            );
                        }
                    }
                }
            }

            // Validate updated materials - check for duplicate orders and required fields
            // Collect all IDs being updated to allow order swapping
            $updatedIds = array_column($updated, 'id');

            foreach ($updated as $index => $material) {
                // Validate material content/file based on is_material_updated flag
                if (isset($material['is_material_updated']) && $material['is_material_updated']) {
                    if ($material['material_type'] === 'text') {
                        if (empty($material['material']['content'] ?? null)) {
                            $validator->errors()->add(
                                "updated.{$index}.material.content",
                                "Content is required when updating text materials."
                            );
                        }
                    } elseif ($material['material_type'] === 'file') {
                        if (empty($material['material']['file'] ?? null)) {
                            $validator->errors()->add(
                                "updated.{$index}.material.file",
                                "File is required when updating file materials."
                            );
                        }
                    }
                }

                // Validate order uniqueness
                if (!isset($material['id']) || !isset($material['order'])) {
                    continue;
                }

                $lectureMaterial = DB::table('lecture_materials')
                    ->where('id', $material['id'])
                    ->first();

                if (!$lectureMaterial) {
                    continue;
                }

                $lectureId = $lectureMaterial->lecture_id;
                $order = $material['order'];

                // Check if order already exists for this lecture
                // Exclude the current material AND all other materials being updated in this batch
                // This allows order swapping within the same request
                $existingOrder = DB::table('lecture_materials')
                    ->where('lecture_id', $lectureId)
                    ->where('order', $order)
                    ->whereNotIn('id', $updatedIds)
                    ->exists();

                if ($existingOrder) {
                    $validator->errors()->add(
                        "updated.{$index}.order",
                        "The order {$order} already exists for this lecture."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            // New materials messages
            'new.*.lecture_id.required' => 'Lecture ID is required for each material.',
            'new.*.lecture_id.exists' => 'The selected lecture does not exist.',
            'new.*.material_type.required' => 'Material type is required.',
            'new.*.material_type.in' => 'Material type must be either text or file.',
            'new.*.order.required' => 'Order is required for each material.',
            'new.*.order.integer' => 'Order must be an integer.',
            'new.*.order.min' => 'Order must be at least 1.',
            'new.*.material_content.required_if' => 'Content is required for text materials.',
            'new.*.material_file.required_if' => 'File is required for file materials.',
            'new.*.material_file.mimes' => 'File must be of type: pdf, doc, docx, xlsx, mkv, mp4, jpg, jpeg, png.',
            'new.*.material_file.max' => 'File size must not exceed 300MB.',

            // Updated materials messages
            'updated.*.id.required' => 'Material ID is required for updates.',
            'updated.*.id.exists' => 'The selected material does not exist.',
            'updated.*.material_type.required' => 'Material type is required.',
            'updated.*.material_type.in' => 'Material type must be either text or file.',
            'updated.*.order.integer' => 'Order must be an integer.',
            'updated.*.order.min' => 'Order must be at least 1.',
            'updated.*.is_material_updated.required' => 'is_material_updated flag is required.',
            'updated.*.is_material_updated.boolean' => 'is_material_updated must be a boolean.',
            'updated.*.material.content.required_if' => 'Content is required for text materials.',
            'updated.*.material.file.required_if' => 'File is required for file materials.',
            'updated.*.material.file.mimes' => 'File must be of type: pdf, doc, docx, xlsx, mkv, mp4, jpg, jpeg, png.',
            'updated.*.material.file.max' => 'File size must not exceed 300MB.',

            // Deleted materials messages
            'deleted.*.required' => 'Material ID is required for deletion.',
            'deleted.*.exists' => 'The selected material does not exist.',
        ];
    }
}
