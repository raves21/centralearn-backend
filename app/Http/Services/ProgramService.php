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
            return new ProgramResource($this->programRepo->create(
                [...$formData, 'image_url' => $image->url],
                relationships: ['department']
            ));
        }
        return new ProgramResource($this->programRepo->create(
            $formData,
            relationships: ['department']
        ));
    }

    public function findById(string $id)
    {
        return new ProgramResource($this->programRepo->findById(
            $id,
            relationships: ['department']
        ));
    }

    public function updateById(string $id, array $formData)
    {
        $program = $this->programRepo->findById($id);

        if (isset($formData['image'])) {
            //delete previous image
            $this->fileAttachmentRepo->deleteByFilter(['url' => $program->image_url]);

            //upload new image
            $newImage = $this->fileAttachmentRepo->uploadAndCreate(file: $formData['image'], type: 'image');
            return new ProgramResource($this->programRepo->updateById(
                id: $id,
                formData: [...$formData, 'image_url' => $newImage->url],
                relationships: ['department']
            ));
        }
        return new ProgramResource($this->programRepo->updateById(
            id: $id,
            formData: $formData,
            relationships: ['department']
        ));
    }

    public function deleteById(string $id)
    {
        return $this->programRepo->deleteById($id);
    }
}
