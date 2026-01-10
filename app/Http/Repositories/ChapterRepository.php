<?php

namespace App\Http\Repositories;

use App\Models\Chapter;

class ChapterRepository extends BaseRepository
{
    public function __construct(Chapter $chapter)
    {
        parent::__construct($chapter);
    }

    public function getContentCount(Chapter $chapter)
    {
        return $chapter->contents()->count();
    }

    public function getAll(
        array $relationships = [],
        array $filters = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        if (in_array('contents', $relationships)) {
            $relationships = array_diff($relationships, ['contents']);
            $relationships['contents'] = function ($query) {
                $query->orderBy('order');
            };
        }

        return parent::getAll($relationships, $filters, $orderBy, $sortDirection, $paginate);
    }
}
