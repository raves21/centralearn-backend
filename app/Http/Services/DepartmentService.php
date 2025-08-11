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
        return DepartmentResource::collection($this->departmentRepo->getAll(filters: $filters));
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $file = $this->fileAttachmentRepo->uploadAndCreate(file: $formData['image'], type: 'image');
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
        if (isset($formData['image'])) {
            //delete previous image
            if ($department->image_url) {
                $this->fileAttachmentRepo->deleteByFilter(['url' => $department->image_url]);
            }
            //upload new image
            $newImage = $this->fileAttachmentRepo->uploadAndCreate(file: $formData['image'], type: 'image');
            $updatedDepartment = $this->departmentRepo->updateById($id, [...$formData, 'image_url' => $newImage->url]);
        } else {
            $updatedDepartment = $this->departmentRepo->updateById($id, $formData);
        }
        return new DepartmentResource($updatedDepartment);
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
