<?php

namespace App\Http\Repositories;

use App\Models\CourseChapter;

class CourseChapterRepository extends BaseRepository
{
    public function __construct(CourseChapter $courseChapter)
    {
        parent::__construct($courseChapter);
    }
}
