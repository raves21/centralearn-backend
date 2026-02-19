<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\LectureMaterialRepository;
use App\Http\Repositories\TextAttachmentRepository;
use App\Http\Resources\LectureMaterialResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\FileAttachment;
use App\Models\LectureMaterial;
use App\Models\TextAttachment;
use Illuminate\Support\Arr;

class LectureMaterialService
{
    public function __construct(
        private LectureMaterialRepository $lectureMaterialRepo,
        private TextAttachmentRepository $textAttachmentRepo,
        private FileAttachmentRepository $fileAttachmentRepo
    ) {}

    public function getAll(array $filters)
    {
        return LectureMaterialResource::collection($this->lectureMaterialRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: Arr::get($filters, 'paginate', true)
        ));
    }

    // public function create(array $formData)
    // {
    //     switch ($formData['material_type']) {
    //         case 'text':
    //             $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['material_content']]);
    //             $newLectureMaterial = $this->lectureMaterialRepo->create([
    //                 ...$formData,
    //                 'materialable_type' => TextAttachment::class,
    //                 'materialable_id' => $newTextAttachment->id
    //             ]);
    //             break;
    //         case 'file':
    //             $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material_file']);
    //             $newLectureMaterial = $this->lectureMaterialRepo->create([
    //                 ...$formData,
    //                 'materialable_type' => FileAttachment::class,
    //                 'materialable_id' => $newFileAttachment->id
    //             ]);
    //             break;
    //     }
    //     return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($newLectureMaterial));
    // }

    public function processBulk(array $formData)
    {
        DB::beginTransaction();

        try {
            $lectureId = $formData['lecture_id'];
            $incomingMaterials = $formData['materials'] ?? [];

            $results = [
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
            ];

            // 1. Identify Deletions
            // Get all existing material IDs for this lecture
            $existingMaterials = $this->lectureMaterialRepo->getAll(
                filters: ['lecture_id' => $lectureId],
                paginate: false
            );
            $existingIds = $existingMaterials->pluck('id')->toArray();

            // Get IDs from payload (filter out nulls which are new items)
            $incomingIds = array_filter(array_column($incomingMaterials, 'id'));

            // Calculate IDs to delete
            $idsToDelete = array_diff($existingIds, $incomingIds);

            // Execute Deletions
            foreach ($idsToDelete as $id) {
                $lectureMaterial = $this->lectureMaterialRepo->findById($id);
                $this->lectureMaterialRepo->deleteMorph(
                    morphType: $lectureMaterial->materialable_type,
                    morphId: $lectureMaterial->materialable_id
                );
                $this->lectureMaterialRepo->deleteById($id);
                $results['deleted']++;
            }

            // 2. Process Upserts (Create & Update)
            if (!empty($incomingIds)) {
                // Temporarily negate orders to avoid unique constraint violations during reordering
                LectureMaterial::whereIn('id', $incomingIds)
                    ->update(['order' => DB::raw('`order` * -1')]);
            }

            foreach ($incomingMaterials as $index => $material) {
                // Determine if Create or Update
                if (isset($material['id'])) {
                    // --- UPDATE LOGIC ---
                    $id = $material['id'];
                    $lectureMaterial = $this->lectureMaterialRepo->findById($id);

                    if ($lectureMaterial) {
                        // Check for Type Change
                        $currentType = ($lectureMaterial->materialable_type === TextAttachment::class) ? 'text' : 'file';
                        $newType = $material['material_type'];

                        if ($currentType !== $newType) {
                            // Delete old morph
                            $this->lectureMaterialRepo->deleteMorph(
                                $lectureMaterial->materialable_type,
                                $lectureMaterial->materialable_id
                            );

                            // Create new morph
                            if ($newType === 'text') {
                                $newAttachment = $this->textAttachmentRepo->create(['content' => $material['material_content']]);
                                $morphClass = TextAttachment::class;
                            } else {
                                $newAttachment = $this->fileAttachmentRepo->uploadAndCreate($material['material_file']['new_file']);
                                $morphClass = FileAttachment::class;
                            }

                            $this->lectureMaterialRepo->updateById($id, [
                                'order' => $material['order'],
                                'materialable_id' => $newAttachment->id,
                                'materialable_type' => $morphClass
                            ]);
                        } else {
                            // Same Type Update
                            $this->lectureMaterialRepo->updateById($id, ['order' => $material['order']]);

                            if ($newType === 'text') {
                                // Update text content if provided
                                $this->textAttachmentRepo->updateById(
                                    $lectureMaterial->materialable_id,
                                    ['content' => $material['material_content']]
                                );
                            } else {
                                // File: Only update if new file provided
                                if (isset($material['material_file']['new_file'])) {
                                    $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
                                    $newAttachment = $this->fileAttachmentRepo->uploadAndCreate($material['material_file']['new_file']);
                                    $this->lectureMaterialRepo->updateById($id, [
                                        'materialable_id' => $newAttachment->id
                                    ]);
                                }
                            }
                        }
                        $results['updated']++;
                    }
                } else {
                    // --- CREATE LOGIC ---
                    switch ($material['material_type']) {
                        case 'text':
                            $newTextAttachment = $this->textAttachmentRepo->create(['content' => $material['material_content']]);
                            $this->lectureMaterialRepo->create([
                                'lecture_id' => $lectureId,
                                'order' => $material['order'],
                                'materialable_type' => TextAttachment::class,
                                'materialable_id' => $newTextAttachment->id
                            ]);
                            break;
                        case 'file':
                            $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($material['material_file']['new_file']);
                            $this->lectureMaterialRepo->create([
                                'lecture_id' => $lectureId,
                                'order' => $material['order'],
                                'materialable_type' => FileAttachment::class,
                                'materialable_id' => $newFileAttachment->id
                            ]);
                            break;
                    }
                    $results['created']++;
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


    // public function updateById(string $id, array $formData)
    // {
    //     $lectureMaterial = $this->lectureMaterialRepo->findById($id);
    //     if (!$formData['is_material_updated']) {
    //         //if material is unchanged
    //         return new LectureMaterialResource($this->lectureMaterialRepo->updateById($id, $formData));
    //     }

    //     $prevMaterialType = match ($lectureMaterial->materialable_type) {
    //         FileAttachment::class => 'file',
    //         TextAttachment::class => 'text'
    //     };

    //     if ($prevMaterialType !== $formData['material_type']) {
    //         //if user changed the material type
    //         //delete previous material
    //         $this->lectureMaterialRepo->deleteMorph(
    //             morphType: $lectureMaterial->materialable_type,
    //             morphId: $lectureMaterial->materialable_id
    //         );

    //         switch ($formData['material_type']) {
    //             case 'text':
    //                 $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['material']['content']]);
    //                 $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
    //                     ...$formData,
    //                     'materialable_id' => $newTextAttachment->id,
    //                     'materialable_type' => TextAttachment::class
    //                 ]);
    //                 break;
    //             case 'file':
    //                 $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
    //                 $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
    //                     ...$formData,
    //                     'materialable_id' => $newFileAttachment->id,
    //                     'materialable_type' => FileAttachment::class
    //                 ]);
    //                 break;
    //         }
    //     } else {
    //         //if material type remained the same
    //         switch ($formData['material_type']) {
    //             case 'text':
    //                 $this->textAttachmentRepo->updateById(
    //                     $lectureMaterial->materialable_id,
    //                     ['content' => $formData['material']['content']]
    //                 );
    //                 $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, $formData);
    //                 break;
    //             case 'file':
    //                 //delete previous
    //                 $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
    //                 //upload new
    //                 $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
    //                 $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
    //                     ...$formData,
    //                     'materialable_id' => $newFileAttachment->id,
    //                     'materialable_type' => FileAttachment::class
    //                 ]);
    //                 break;
    //         }
    //     }
    //     return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($updatedLectureMaterial));
    // }
}
