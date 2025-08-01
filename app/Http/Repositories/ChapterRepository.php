<?php

namespace App\Http\Repositories;

use App\Models\Chapter;

class ChapterRepository extends BaseRepository
{
    public function __construct(Chapter $chapter)
    {
        parent::__construct($chapter);
    }
}
