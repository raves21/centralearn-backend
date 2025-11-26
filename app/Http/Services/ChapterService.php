<?php

namespace App\Http\Services;

use App\Http\Repositories\ChapterRepository;
use App\Http\Resources\ChapterResource;
use Illuminate\Support\Arr;

class ChapterService
{
    private $chapterRepo;

    public function __construct(
        ChapterRepository $chapterRepo,
    ) {
        $this->chapterRepo = $chapterRepo;
    }

    public function getAll(array $filters)
    {
        $includeChapterContentsFilter = Arr::get($filters, 'include_chapter_contents', true);

        return ChapterResource::collection($this->chapterRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            relationships: $includeChapterContentsFilter ? ['contents'] : [],
            paginate: false
        ));
    }

    public function create(array $formData)
    {
        return new ChapterResource($this->chapterRepo->create($formData));
    }

    public function findById(string $id)
    {
        return new ChapterResource($this->chapterRepo->findById($id));
    }

    public function updateById(string $id, array $formData)
    {
        return new ChapterResource($this->chapterRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->chapterRepo->deleteById($id);
    }
}
