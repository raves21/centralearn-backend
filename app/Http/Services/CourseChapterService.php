<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseChapterRepository;
use App\Http\Resources\CourseChapterResource;

class CourseChapterService
{
    protected $courseChapterRepo;

    public function __construct(CourseChapterRepository $courseChapterRepo)
    {
        $this->courseChapterRepo = $courseChapterRepo;
    }

    public function findById(string $id)
    {
        return new CourseChapterResource($this->courseChapterRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        return new CourseChapterResource($this->courseChapterRepo->updateById($id, $formData));
    }

    public function create(array $formData)
    {
        return new CourseChapterResource($this->courseChapterRepo->create($formData));
    }

    public function deleteById(string $id)
    {
        return $this->courseChapterRepo->deleteById($id);
    }
}
