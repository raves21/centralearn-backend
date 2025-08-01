<?php

namespace App\Http\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $course)
    {
        parent::__construct($course);
    }
}
