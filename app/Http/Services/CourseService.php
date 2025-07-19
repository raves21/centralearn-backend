<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseChapterRepository;
use App\Http\Repositories\CourseRepository;

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

    public function getAll(array $filters)
    {
        return $this->courseRepo->getAll(relationships: ['department:id,name,code'], filters: $filters);
    }

    public function getChapters(string $courseId)
    {
        return $this->courseChapterRepo->getCourseChaptersByCourseId($courseId);
    }
}
