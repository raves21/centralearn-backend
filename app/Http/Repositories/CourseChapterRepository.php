<?php

namespace App\Http\Repositories;

use App\Models\CourseChapter;

class CourseChapterRepository extends BaseRepository
{
    public function __construct(CourseChapter $courseChapter)
    {
        parent::__construct($courseChapter);
    }

    public function getCourseChaptersByCourseId(string $courseId)
    {
        return CourseChapter::where('course_id', $courseId)->with('course')->get();
    }
}
