<?php

namespace App\Http\Services;

use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\SemesterResource;
use Illuminate\Support\Arr;

class SemesterService
{

    public function __construct(
        private SemesterRepository $semesterRepo
    ) {}

    public function getAll(array $filters)
    {
        return SemesterResource::collection($this->semesterRepo->getAll(
            orderBy: Arr::get($filters, 'order_by', 'created_at'),
            filters: $filters,
            paginate: Arr::get($filters, 'paginate', true)
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
