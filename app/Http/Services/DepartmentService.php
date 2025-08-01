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

    public function findById(string $id)
    {
        return new DepartmentResource($this->departmentRepo->findById($id));
    }
}
