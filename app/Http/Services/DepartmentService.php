<?php

namespace App\Http\Services;

use App\Http\Repositories\BaseRepository;
use App\Http\Repositories\DepartmentRepository;
use App\Http\Resources\DepartmentResource;

class DepartmentService
{
    private $departmentRepo;

    public function __construct(DepartmentRepository $departmentRepo)
    {
        $this->departmentRepo = $departmentRepo;
    }

    public function getAll()
    {
        return DepartmentResource::collection($this->departmentRepo->getAll());
    }

    public function create(array $formData)
    {
        return new DepartmentResource($this->departmentRepo->create($formData));
    }

    public function findById(string $id)
    {
        return new DepartmentResource($this->departmentRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        return new DepartmentResource($this->departmentRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->departmentRepo->deleteById($id);
    }
}
