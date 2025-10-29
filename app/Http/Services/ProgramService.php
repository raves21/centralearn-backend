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
        $paginate = $filters['paginate'] ?? null;
        return ProgramResource::collection(
            $this->programRepo->getAll(
                relationships: ['department'],
                filters: $filters,
                paginate: empty($paginate) ? true : $paginate
            )
        );
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $image = $this->fileAttachmentRepo->uploadAndCreate($formData['image']);
            $newProgram = $this->programRepo->create(
                formData: [...$formData, 'image_url' => $image->url],
            );
        } else {
            $newProgram = $this->programRepo->create(
                formData: $formData,
            );
        }
        return new ProgramResource($newProgram);
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
        $payloadImage = $formData['image'] ?? null;

        if ($program->image_url) {
            if ($payloadImage) {
                // Delete the old image in any case
                $this->fileAttachmentRepo->deleteByFilter(['url' => $program->image_url]);

                if ($payloadImage === "__DELETED__") {
                    $formData['image_url'] = null;
                } else {
                    // Upload new image and update image_url
                    $newImage = $this->fileAttachmentRepo->uploadAndCreate($payloadImage);
                    $formData['image_url'] = $newImage->url;
                }
            }
        } else {
            if ($payloadImage) {
                $newImage = $this->fileAttachmentRepo->uploadAndCreate($payloadImage);
                $formData['image_url'] = $newImage->url;
            }
        }

        return new ProgramResource($this->programRepo->updateById(
            id: $id,
            formData: $formData,
            relationships: ['department']
        ));
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
