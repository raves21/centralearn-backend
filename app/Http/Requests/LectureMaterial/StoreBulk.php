<?php

namespace App\Http\Requests\LectureMaterial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreBulk extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.lecture_id' => ['required', 'exists:lectures,id'],
            'materials.*.material_type' => ['required', 'in:text,file'],
            'materials.*.order' => [
                'required',
                'integer',
                'min:1',
            ],
            'materials.*.material_content' => [
                'required_if:materials.*.material_type,text',
                'string',
            ],
            'materials.*.material_file' => [
                'required_if:materials.*.material_type,file',
                'file',
                'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                'max:307200',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $materials = $this->input('materials', []);

            // Group materials by lecture_id to check for duplicate orders within each lecture
            $lectureGroups = [];
            foreach ($materials as $index => $material) {
                $lectureId = $material['lecture_id'] ?? null;
                $order = $material['order'] ?? null;

                if ($lectureId && $order) {
                    if (!isset($lectureGroups[$lectureId])) {
                        $lectureGroups[$lectureId] = [];
                    }

                    if (in_array($order, $lectureGroups[$lectureId])) {
                        $validator->errors()->add(
                            "materials.{$index}.order",
                            "The order value {$order} is duplicated for lecture {$lectureId}."
                        );
                    }

                    $lectureGroups[$lectureId][] = $order;
                }
            }

            // Check for existing orders in database
            foreach ($lectureGroups as $lectureId => $orders) {
                $existingOrders = DB::table('lecture_materials')
                    ->where('lecture_id', $lectureId)
                    ->whereIn('order', $orders)
                    ->pluck('order')
                    ->toArray();

                if (!empty($existingOrders)) {
                    foreach ($materials as $index => $material) {
                        if (
                            isset($material['lecture_id']) &&
                            $material['lecture_id'] === $lectureId &&
                            isset($material['order']) &&
                            in_array($material['order'], $existingOrders)
                        ) {
                            $validator->errors()->add(
                                "materials.{$index}.order",
                                "The order {$material['order']} already exists for this lecture."
                            );
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'materials.required' => 'At least one material is required.',
            'materials.*.lecture_id.required' => 'Lecture ID is required for each material.',
            'materials.*.lecture_id.exists' => 'The selected lecture does not exist.',
            'materials.*.material_type.required' => 'Material type is required.',
            'materials.*.material_type.in' => 'Material type must be either text or file.',
            'materials.*.order.required' => 'Order is required for each material.',
            'materials.*.order.integer' => 'Order must be an integer.',
            'materials.*.order.min' => 'Order must be at least 1.',
            'materials.*.material_content.required_if' => 'Content is required for text materials.',
            'materials.*.material_file.required_if' => 'File is required for file materials.',
            'materials.*.material_file.mimes' => 'File must be of type: pdf, doc, docx, xlsx, mkv, mp4, jpg, jpeg, png.',
            'materials.*.material_file.max' => 'File size must not exceed 300MB.',
        ];
    }
}
