<?php

namespace App\Http\Services;

use App\Http\Repositories\ChapterContentRepository;
use App\Http\Resources\ChapterContentResource;

class ChapterContentService
{
    protected $chapterContentRepo;

    public function __construct(ChapterContentRepository $chapterContentRepo)
    {
        $this->chapterContentRepo = $chapterContentRepo;
    }

    public function create(array $formData)
    {
        return new ChapterContentResource($this->chapterContentRepo->create($formData));
    }

    public function findById(string $id)
    {
        return new ChapterContentResource($this->chapterContentRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        return new ChapterContentResource($this->chapterContentRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->chapterContentRepo->deleteById($id);
    }
}
