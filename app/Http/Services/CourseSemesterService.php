<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseSemesterRepository;
use App\Http\Resources\CourseSemesterResource;

class CourseSemesterService
{
    private $courseSemesterRepo;

    public function __construct(CourseSemesterRepository $courseSemesterRepo)
    {
        $this->courseSemesterRepo = $courseSemesterRepo;
    }

    public function getAll(array $filters)
    {
        return CourseSemesterResource::collection($this->courseSemesterRepo->getAll(filters: $filters, relationships: ['course', 'semester']));
    }

    public function findById(string $id)
    {
        return new CourseSemesterResource($this->courseSemesterRepo->findById($id, relationships: ['course', 'semester']));
    }

    public function create(array $formData)
    {
        return new CourseSemesterResource($this->courseSemesterRepo->create($formData, relationships: ['course', 'semester']));
    }

    public function updateById(string $id, array $formData)
    {
        return new CourseSemesterResource($this->courseSemesterRepo->updateById($id, $formData, relationships: ['course', 'semester']));
    }

    public function deleteById(string $id)
    {
        return $this->courseSemesterRepo->deleteById($id);
    }
}
