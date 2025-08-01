<?php

namespace App\Http\Repositories;

use App\Models\Assessment;
use App\Models\ChapterContent;
use App\Models\Lecture;

class ChapterContentRepository extends BaseRepository
{
    public function __construct(ChapterContent $chapterContent)
    {
        parent::__construct($chapterContent);
    }
}
