<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\LectureMaterialRepository;
use App\Http\Repositories\TextAttachmentRepository;
use App\Http\Resources\LectureMaterialResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\FileAttachment;
use App\Models\TextAttachment;
use Illuminate\Support\Arr;

class LectureMaterialService
{
    private $lectureMaterialRepo;
    private $textAttachmentRepo;
    private $fileAttachmentRepo;

    public function __construct(
        LectureMaterialRepository $lectureMaterialRepo,
        TextAttachmentRepository $textAttachmentRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->lectureMaterialRepo = $lectureMaterialRepo;
        $this->textAttachmentRepo = $textAttachmentRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return LectureMaterialResource::collection($this->lectureMaterialRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: Arr::get($filters, 'paginate', true)
        ));
    }

    public function findById(string $id)
    {
        return new LectureMaterialResource($this->lectureMaterialRepo->findById($id));
    }

    public function create(array $formData)
    {
        switch ($formData['material_type']) {
            case 'text':
                $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['material_content']]);
                $newLectureMaterial = $this->lectureMaterialRepo->create([
                    ...$formData,
                    'materialable_type' => TextAttachment::class,
                    'materialable_id' => $newTextAttachment->id
                ]);
                break;
            case 'file':
                $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material_file']);
                $newLectureMaterial = $this->lectureMaterialRepo->create([
                    ...$formData,
                    'materialable_type' => FileAttachment::class,
                    'materialable_id' => $newFileAttachment->id
                ]);
                break;
        }
        return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($newLectureMaterial));
    }

    public function processBulk(array $formData)
    {
        DB::beginTransaction();

        try {
            $results = [
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
            ];

            // Handle NEW materials
            if (!empty($formData['new'])) {
                foreach ($formData['new'] as $material) {
                    switch ($material['material_type']) {
                        case 'text':
                            $newTextAttachment = $this->textAttachmentRepo->create(['content' => $material['material_content']]);
                            $this->lectureMaterialRepo->create([
                                'lecture_id' => $material['lecture_id'],
                                'order' => $material['order'],
                                'materialable_type' => TextAttachment::class,
                                'materialable_id' => $newTextAttachment->id
                            ]);
                            break;
                        case 'file':
                            $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($material['material_file']);
                            $this->lectureMaterialRepo->create([
                                'lecture_id' => $material['lecture_id'],
                                'order' => $material['order'],
                                'materialable_type' => FileAttachment::class,
                                'materialable_id' => $newFileAttachment->id
                            ]);
                            break;
                    }
                    $results['created']++;
                }
            }

            // Handle UPDATED materials
            if (!empty($formData['updated'])) {
                // Two-pass update to handle order swapping without unique constraint violations

                // First pass: Set all orders to temporary negative values to free up the order numbers
                foreach ($formData['updated'] as $index => $material) {
                    if (isset($material['order'])) {
                        $this->lectureMaterialRepo->updateById($material['id'], [
                            'order' => - ($index + 1) // Use negative index to avoid conflicts
                        ]);
                    }
                }

                // Second pass: Update to final values (order + content/file if needed)
                foreach ($formData['updated'] as $material) {
                    $id = $material['id'];
                    $lectureMaterial = $this->lectureMaterialRepo->findById($id);

                    if (!$material['is_material_updated']) {
                        // If material is unchanged, just update order if provided
                        $updateData = [];
                        if (isset($material['order'])) {
                            $updateData['order'] = $material['order'];
                        }
                        if (!empty($updateData)) {
                            $this->lectureMaterialRepo->updateById($id, $updateData);
                        }
                    } else {
                        // Material content/file is being updated
                        switch ($material['material_type']) {
                            case 'text':
                                // Update text content
                                $this->textAttachmentRepo->updateById(
                                    $lectureMaterial->materialable_id,
                                    ['content' => $material['material']['content']]
                                );
                                $updateData = [];
                                if (isset($material['order'])) {
                                    $updateData['order'] = $material['order'];
                                }
                                if (!empty($updateData)) {
                                    $this->lectureMaterialRepo->updateById($id, $updateData);
                                }
                                break;
                            case 'file':
                                // Delete old file and upload new
                                $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
                                $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($material['material']['file']);
                                $updateData = [
                                    'materialable_id' => $newFileAttachment->id,
                                ];
                                if (isset($material['order'])) {
                                    $updateData['order'] = $material['order'];
                                }
                                $this->lectureMaterialRepo->updateById($id, $updateData);
                                break;
                        }
                    }
                    $results['updated']++;
                }
            }

            // Handle DELETED materials
            if (!empty($formData['deleted'])) {
                foreach ($formData['deleted'] as $id) {
                    $lectureMaterial = $this->lectureMaterialRepo->findById($id);
                    // Delete the associated material (text or file)
                    $this->lectureMaterialRepo->deleteMorph(
                        morphType: $lectureMaterial->materialable_type,
                        morphId: $lectureMaterial->materialable_id
                    );
                    // Delete the lecture material record
                    $this->lectureMaterialRepo->deleteById($id);
                    $results['deleted']++;
                }
            }

            DB::commit();
            return [
                'message' => 'Bulk operations completed successfully',
                'results' => $results
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function updateById(string $id, array $formData)
    {
        $lectureMaterial = $this->lectureMaterialRepo->findById($id);
        if (!$formData['is_material_updated']) {
            //if material is unchanged
            return new LectureMaterialResource($this->lectureMaterialRepo->updateById($id, $formData));
        }

        $prevMaterialType = match ($lectureMaterial->materialable_type) {
            FileAttachment::class => 'file',
            TextAttachment::class => 'text'
        };

        if ($prevMaterialType !== $formData['material_type']) {
            //if user changed the material type
            //delete previous material
            $this->lectureMaterialRepo->deleteMorph(
                morphType: $lectureMaterial->materialable_type,
                morphId: $lectureMaterial->materialable_id
            );

            switch ($formData['material_type']) {
                case 'text':
                    $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['material']['content']]);
                    $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newTextAttachment->id,
                        'materialable_type' => TextAttachment::class
                    ]);
                    break;
                case 'file':
                    $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                    $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newFileAttachment->id,
                        'materialable_type' => FileAttachment::class
                    ]);
                    break;
            }
        } else {
            //if material type remained the same
            switch ($formData['material_type']) {
                case 'text':
                    $this->textAttachmentRepo->updateById(
                        $lectureMaterial->materialable_id,
                        ['content' => $formData['material']['content']]
                    );
                    $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, $formData);
                    break;
                case 'file':
                    //delete previous
                    $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
                    //upload new
                    $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                    $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newFileAttachment->id,
                        'materialable_type' => FileAttachment::class
                    ]);
                    break;
            }
        }
        return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($updatedLectureMaterial));
    }

    public function deleteById(string $id)
    {
        $lectureMaterial = $this->lectureMaterialRepo->findById($id);
        //delete material
        $this->lectureMaterialRepo->deleteMorph(
            morphType: $lectureMaterial->materialable_type,
            morphId: $lectureMaterial->materialable_id
        );
        return $this->lectureMaterialRepo->deleteById($id);
    }
}
