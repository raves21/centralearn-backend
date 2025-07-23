<?php

namespace App\Http\Repositories;

use App\Models\ChapterContent;

class ChapterContentRepository extends BaseRepository
{
    public function __construct(ChapterContent $chapterContent)
    {
        parent::__construct($chapterContent);
    }
}
