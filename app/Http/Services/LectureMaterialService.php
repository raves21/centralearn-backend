<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\LectureMaterialRepository;
use App\Http\Repositories\TextAttachmentRepository;
use App\Http\Resources\LectureMaterialResource;
use Illuminate\Support\Str;
use App\Models\FileAttachment;
use App\Models\TextAttachment;

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
            paginate: $filters['paginate']
        ));
    }

    public function findById(string $id)
    {
        return new LectureMaterialResource($this->lectureMaterialRepo->findById($id));
    }

    public function create(array $formData)
    {
        if ($formData['material_type'] === 'text') {
            $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['material']['content']]);
            $newLectureMaterial = $this->lectureMaterialRepo->create([
                ...$formData,
                'materialable_type' => TextAttachment::class,
                'materialable_id' => $newTextAttachment->id
            ]);
            return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($newLectureMaterial));
        } else {
            $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
            $newLectureMaterial = $this->lectureMaterialRepo->create([
                ...$formData,
                'materialable_type' => FileAttachment::class,
                'materialable_id' => $newFileAttachment->id
            ]);
            return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($newLectureMaterial));
        }
    }

    public function updateById(string $id, array $formData)
    {
        $lectureMaterial = $this->lectureMaterialRepo->findById($id);
        if (!$formData['is_material_updated']) {
            //if material is unchanged
            return new LectureMaterialResource($this->lectureMaterialRepo->updateById($id, $formData));
        }

        $materialType = match ($lectureMaterial->materialable_type) {
            FileAttachment::class => 'file',
            TextAttachment::class => 'text'
        };

        if ($materialType !== $formData['material_type']) {
            //if user changed the material type
            if ($formData['material_type'] === 'text') {
                //if before it was file but now updates to text,
                //delete previous file attachment
                $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
                //and create new text attachment
                $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['material']['content']]);
                $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
                    ...$formData,
                    'materialable_id' => $newTextAttachment->id,
                    'materialable_type' => TextAttachment::class
                ]);
                return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($updatedLectureMaterial));
            } else {
                //if before it was text but now updates to file
                //delete previous text attachment
                $this->textAttachmentRepo->deleteById($lectureMaterial->materialable_id);
                //and create new file attachment
                $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
                    ...$formData,
                    'materialable_id' => $newFileAttachment->id,
                    'materialable_type' => FileAttachment::class
                ]);
                return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($updatedLectureMaterial));
            }
        } else {
            //if same material type
            if ($formData['material_type'] === 'text') {
                $this->textAttachmentRepo->updateById(
                    $lectureMaterial->materialable_id,
                    ['content' => $formData['material']['content']]
                );
                $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, $formData);
                return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($updatedLectureMaterial));
            } else {
                //delete previous file attachment
                $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
                //upload new file attachment
                $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                $updatedLectureMaterial = $this->lectureMaterialRepo->updateById($id, [
                    ...$formData,
                    'materialable_id' => $newFileAttachment->id,
                    'materialable_type' => FileAttachment::class
                ]);
                return new LectureMaterialResource($this->lectureMaterialRepo->getFresh($updatedLectureMaterial));
            }
        }
    }

    public function deleteById(string $id)
    {
        $lectureMaterial = $this->lectureMaterialRepo->findById($id);
        //delete associated material
        if ($lectureMaterial->materialable_type === FileAttachment::class) {
            $this->fileAttachmentRepo->deleteById($lectureMaterial->materialable_id);
        } else {
            $this->textAttachmentRepo->deleteById($lectureMaterial->materialable_id);
        }
        return $this->lectureMaterialRepo->deleteById($id);
    }
}
