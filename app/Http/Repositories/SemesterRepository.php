<?php

namespace App\Http\Repositories;

use App\Models\CourseSemester;
use App\Models\Semester;

class SemesterRepository extends BaseRepository
{

    public function __construct(Semester $semester)
    {
        parent::__construct($semester);
    }

    public function getStudentEnrolledSemesters(string $studentId)
    {
        return Semester::whereHas('courseSemesters.studentEnrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
            ->orderByDesc('start_date')
            ->get();
    }

    public function getInstructorAssignedSemesters(string $instructorId)
    {
        return Semester::whereHas('courseSemesters.instructorAssignments', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
            ->orderByDESC('start_date')
            ->get();
    }
}
