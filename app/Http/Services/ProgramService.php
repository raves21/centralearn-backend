<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\ProgramRepository;
use App\Http\Resources\ProgramResource;

class ProgramService
{
    private $programRepo;
    private $fileAttachmentRepo;

    public function __construct(
        ProgramRepository $programRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->programRepo = $programRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return ProgramResource::collection(
            $this->programRepo->getAll(relationships: ['department'], filters: $filters)
        );
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $image = $this->fileAttachmentRepo->uploadAndCreate($formData['image'], 'image');
            $newProgram = $this->programRepo->create(
                formData: [...$formData, 'image_url' => $image->url],
                relationships: ['departments']
            );
        } else {
            $newProgram = $this->programRepo->create(
                formData: $formData,
                relationships: ['departments']
            );
        }
        return new ProgramResource($newProgram);
    }

    public function findById(string $id)
    {
        return new ProgramResource($this->programRepo->findById(
            $id,
            relationships: ['departments']
        ));
    }

    public function updateById(string $id, array $formData)
    {
        $program = $this->programRepo->findById($id);
        if (isset($formData['image'])) {
            //delete previous image
            if ($program->image_url) {
                $this->fileAttachmentRepo->deleteByFilter(['url' => $program->image_url]);
            }
            //upload new image
            $newImage = $this->fileAttachmentRepo->uploadAndCreate(file: $formData['image'], type: 'image');
            $updatedProgram = $this->programRepo->updateById(
                id: $id,
                formData: [...$formData, 'image_url' => $newImage->url],
                relationships: ['department']
            );
        } else {
            $updatedProgram = $this->programRepo->updateById(
                id: $id,
                formData: $formData,
                relationships: ['department']
            );
        }
        return new ProgramResource($updatedProgram);
    }

    public function deleteById(string $id)
    {
        $program = $this->programRepo->findById($id);
        if ($program->image_url) {
            $this->fileAttachmentRepo->deleteByFilter(['url' => $program->image_url]);
        }
        return $this->programRepo->deleteById($id);
    }
}
