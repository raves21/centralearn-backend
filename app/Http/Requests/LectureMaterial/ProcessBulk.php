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
            'lecture_id' => ['required', 'exists:lectures,id'],
            'materials' => ['present', 'array'], // 'present' allows empty array (removes all materials)

            'materials.*.id' => ['nullable', 'exists:lecture_materials,id'],
            'materials.*.material_type' => ['required', 'in:text,file'],
            'materials.*.order' => ['required', 'integer', 'min:1'],

            // Text content validation
            'materials.*.material_content' => [
                'required_if:materials.*.material_type,text',
                'nullable',
                'string'
            ],

            'materials.*.material_file' => ['required_if:materials.*.material_type,file', 'array'],
            'materials.*.material_file.kept_file' => ['nullable', 'string'],
            'materials.*.material_file.new_file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xlsx,mkv,mp4,jpg,jpeg,png',
                'max:307200'
            ]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lectureId = $this->input('lecture_id');
            $materials = $this->all()['materials'] ?? [];

            // Track orders to ensure uniqueness within the payload
            $orders = [];

            foreach ($materials as $index => $material) {
                // 1. Validate 'material_file' requirement
                $type = $material['material_type'] ?? null;
                $id = $material['id'] ?? null;

                if ($type === 'file') {
                    $fileData = $material['material_file'] ?? null;
                    $newFile = $fileData['new_file'] ?? null;
                    $keptFile = $fileData['kept_file'] ?? null;

                    if (is_null($id)) {
                        // Case A: New Material -> Must have new_file
                        if (!$newFile) {
                            $validator->errors()->add(
                                "materials.{$index}.material_file.new_file",
                                "A file is required for new materials."
                            );
                        }
                    } else {
                        // Case B: Existing Material -> Must have kept_file OR new_file
                        if (!$keptFile && !$newFile) {
                            $validator->errors()->add(
                                "materials.{$index}.material_file",
                                "Either keep the existing file or upload a new one."
                            );
                        }
                    }
                }

                // 2. Validate valid ID ownership (Security check)
                // Ensure the ID being updated actually belongs to the specified lecture_id
                if ($id) {
                    $exists = DB::table('lecture_materials')
                        ->where('id', $id)
                        ->where('lecture_id', $lectureId)
                        ->exists();

                    if (!$exists) {
                        $validator->errors()->add(
                            "materials.{$index}.id",
                            "The material ID {$id} does not belong to the specified lecture."
                        );
                    }
                }

                // 3. Check for duplicate orders within the payload
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
            }
        });
    }

    public function messages(): array
    {
        return [
            'lecture_id.required' => 'Lecture ID is required.',
            'lecture_id.exists' => 'The selected lecture does not exist.',
            'materials.present' => 'Materials list must be provided.',
            'materials.array' => 'Materials must be an array.',
            'materials.*.id.exists' => 'The selected material does not exist.',
            'materials.*.material_type.required' => 'Material type is required.',
            'materials.*.material_type.in' => 'Material type must be either text or file.',
            'materials.*.order.required' => 'Order is required.',
            'materials.*.order.integer' => 'Order must be an integer.',
            'materials.*.material_content.required_if' => 'Content is required for text materials.',
            'materials.*.material_file.mimes' => 'File must be of type: pdf, doc, docx, xlsx, mkv, mp4, jpg, jpeg, png.',
            'materials.*.material_file.max' => 'File size must not exceed 300MB.',
        ];
    }
}
