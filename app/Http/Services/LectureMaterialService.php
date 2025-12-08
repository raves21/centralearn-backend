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

    public function createBulk(array $formData)
    {
        DB::beginTransaction();

        try {
            foreach ($formData['materials'] as $material) {
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
            }

            DB::commit();
            return ['message' => 'Lecture materials created successfully'];
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
