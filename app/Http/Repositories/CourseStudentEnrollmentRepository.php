<?php

namespace App\Http\Repositories;

use App\Models\CourseStudentEnrollment;

class CourseStudentEnrollmentRepository extends BaseRepository
{
    public function __construct(CourseStudentEnrollment $courseStudentEnrollment)
    {
        parent::__construct($courseStudentEnrollment);
    }
}
