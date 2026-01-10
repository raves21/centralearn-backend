<?php

namespace App\Http\Services;

use App\Http\Repositories\ChapterRepository;
use App\Http\Resources\ChapterResource;
use Illuminate\Support\Facades\DB;
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

    public function getContentCount(string $id)
    {
        return $this->chapterRepo->getContentCount($this->chapterRepo->findById($id));
    }

    public function reorderBulk(array $formData)
    {
        return DB::transaction(function () use ($formData) {
            // First pass: set to temporary negative order to avoid unique constraint violations
            foreach ($formData['chapters'] as $chapter) {
                // Use a negative value derived from the new order to ensure temporary uniqueness
                // assuming new_order is typically positive.
                $tempOrder = -1 * $chapter['new_order'];
                $this->chapterRepo->updateById($chapter['id'], ['order' => $tempOrder]);
            }

            // Second pass: set to final correct order
            foreach ($formData['chapters'] as $chapter) {
                $this->chapterRepo->updateById($chapter['id'], ['order' => $chapter['new_order']]);
            }

            return ['message' => 'reorder bulk success.'];
        });
    }
}
