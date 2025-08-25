<?php

namespace App\Http\Services;

use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\SemesterResource;

class SemesterService
{

    private $semesterRepo;

    public function __construct(SemesterRepository $semesterRepo)
    {
        $this->semesterRepo = $semesterRepo;
    }

    public function getAll(array $filters)
    {
        return SemesterResource::collection($this->semesterRepo->getAll(
            orderBy: $filters['order_by'] ?? 'created_at',
        ));
    }

    public function create(array $formData)
    {
        return new SemesterResource($this->semesterRepo->create($formData));
    }

    public function findById(string $id)
    {
        return new SemesterResource($this->semesterRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        return new SemesterResource($this->semesterRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->semesterRepo->deleteById($id);
    }

    public function updateSemesterGetMinMaxTimestamps(string $id)
    {
        $semester = $this->semesterRepo->findById($id);
        return $this->semesterRepo->updateSemesterGetMinMaxTimestamps($semester);
    }

    public function createSemesterGetMinMaxTimestamps()
    {
        return $this->semesterRepo->createSemesterGetMinMaxTimestamps();
    }
}
