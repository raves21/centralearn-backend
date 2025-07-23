<?php

namespace App\Http\Services;

use App\Http\Repositories\ChapterContentRepository;
use App\Http\Repositories\CourseChapterRepository;
use App\Http\Resources\ChapterContentResource;
use App\Http\Resources\CourseChapterResource;

class CourseChapterService
{
    protected $courseChapterRepo;
    protected $chapterContentRepo;

    public function __construct(
        CourseChapterRepository $courseChapterRepo,
        ChapterContentRepository $chapterContentRepo
    ) {
        $this->courseChapterRepo = $courseChapterRepo;
        $this->chapterContentRepo = $chapterContentRepo;
    }

    public function getContents(string $id)
    {
        $this->courseChapterRepo->ensureExists($id);
        return ChapterContentResource::collection($this->chapterContentRepo->getAll(
            filters: ['chapter_id', $id],
            paginate: false
        ));
    }

    public function create(array $formData)
    {
        return new CourseChapterResource($this->courseChapterRepo->create($formData));
    }

    public function findById(string $id)
    {
        return new CourseChapterResource($this->courseChapterRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        return new CourseChapterResource($this->courseChapterRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->courseChapterRepo->deleteById($id);
    }
}
