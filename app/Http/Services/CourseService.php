<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseChapterRepository;
use App\Http\Repositories\CourseRepository;
use App\Http\Resources\CourseChapterResource;
use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseService
{
    protected $courseRepo;
    protected $courseChapterRepo;

    public function __construct(
        CourseRepository $courseRepo,
        CourseChapterRepository $courseChapterRepo
    ) {
        $this->courseRepo = $courseRepo;
        $this->courseChapterRepo = $courseChapterRepo;
    }

    public function getChapters(string $courseId)
    {
        $this->courseRepo->ensureExists($courseId);
        return CourseChapterResource::collection($this->courseChapterRepo->getAll(
            filters: ['course_id', $courseId],
            paginate: false,
            relationships: ['course']
        ));
    }

    public function getAll(array $filters)
    {
        return CourseResource::collection($this->courseRepo->getAll(
            relationships: ['departments:id,code'],
            filters: $filters
        ));
    }

    public function findById(string $id)
    {
        return new CourseResource($this->courseRepo->findById($id, relationships: ['departments:id,code']));
    }
}
