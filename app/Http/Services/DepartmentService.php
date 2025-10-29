<?php

namespace App\Http\Services;

use App\Http\Repositories\DepartmentRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Resources\DepartmentResource;
use App\Models\FileAttachment;

class DepartmentService
{
    private $departmentRepo;
    private $fileAttachmentRepo;

    public function __construct(
        DepartmentRepository $departmentRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->departmentRepo = $departmentRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        $paginate = $filters['paginate'] ?? null;
        return DepartmentResource::collection($this->departmentRepo->getAll(
            filters: $filters,
            paginate: empty($paginate) ? true : $paginate
        ));
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $file = $this->fileAttachmentRepo->uploadAndCreate(file: $formData['image']);
            $newDepartment = $this->departmentRepo->create([
                ...$formData,
                'image_url' => $file->url
            ]);
        } else {
            $newDepartment = $this->departmentRepo->create($formData);
        }
        return new DepartmentResource($newDepartment);
    }

    public function findById(string $id)
    {
        return new DepartmentResource($this->departmentRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        $department = $this->departmentRepo->findById($id);
        $payloadImage = $formData['image'] ?? null;

        if ($department->image_url) {
            if ($payloadImage) {
                // Delete the old image in any case
                $this->fileAttachmentRepo->deleteByFilter(['url' => $department->image_url]);

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

        return new DepartmentResource($this->departmentRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        $department = $this->departmentRepo->findById($id);
        if ($department->image_url) {
            $this->fileAttachmentRepo->deleteByFilter(['url' => $department->image_url]);
        }
        return $this->departmentRepo->deleteById($id);
    }
}
