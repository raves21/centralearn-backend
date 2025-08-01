<?php

namespace App\Http\Services;

use App\Http\Repositories\LectureMaterialRepository;
use App\Http\Resources\LectureMaterialResource;

class LectureMaterialService
{
    private $lectureMaterialRepo;

    public function __construct(LectureMaterialRepository $lectureMaterialRepo)
    {
        $this->lectureMaterialRepo = $lectureMaterialRepo;
    }

    public function getAll(array $filters)
    {
        return LectureMaterialResource::collection($this->lectureMaterialRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: false
        ));
    }

    public function findById(string $id)
    {
        return new LectureMaterialResource($this->lectureMaterialRepo->findById($id));
    }

    public function create(array $formData)
    {
        return new LectureMaterialResource($this->lectureMaterialRepo->create($formData));
    }

    public function updateById(string $id, array $formData)
    {
        return new LectureMaterialResource($this->lectureMaterialRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->lectureMaterialRepo->deleteById($id);
    }
}
