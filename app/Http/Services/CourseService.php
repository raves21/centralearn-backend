<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseRepository;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseService
{
    private $courseRepo;

    public function __construct(
        CourseRepository $courseRepo,
    ) {
        $this->courseRepo = $courseRepo;
    }

    public function getAll(array $filters)
    {
        return CourseResource::collection($this->courseRepo->getAll(
            relationships: ['departments:id,code'],
            filters: $filters
        ));
    }

    public function create(array $formData)
    {
        return new CourseResource($this->courseRepo->create($formData));
    }

    public function updateById(string $id, array $formData)
    {
        return new CourseResource($this->courseRepo->updateById($id, $formData));
    }

    public function findById(string $id)
    {
        return new CourseResource($this->courseRepo->findById($id, relationships: ['departments:id,code']));
    }

    public function deleteById(string $id)
    {
        return $this->courseRepo->deleteById($id);
    }
}
