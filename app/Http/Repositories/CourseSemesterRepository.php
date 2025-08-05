<?php

namespace App\Http\Repositories;

use App\Models\CourseSemester;
use Illuminate\Support\Collection;

class CourseSemesterRepository extends BaseRepository
{
    public function __construct(CourseSemester $courseSemester)
    {
        parent::__construct($courseSemester);
    }

    public function getStudentEnrolledCourses(
        string $studentId,
        array $filters,
        ?Collection $studentEnrolledSemesters = null
    ) {
        $semesterId = $filters['semester_id'] ?? null;
        $courseName = $filters['course_name'] ?? null;

        return CourseSemester::whereHas('studentEnrollments', function ($q) use ($semesterId, $studentEnrolledSemesters, $studentId) {
            $q->where('student_id', $studentId);
            $q->when($semesterId || $studentEnrolledSemesters->isNotEmpty(), function ($q) use ($semesterId, $studentEnrolledSemesters) {
                $q->where('semester_id', $semesterId ?? $studentEnrolledSemesters->first()->id);
            });
        })
            ->when($courseName, function ($q) use ($courseName) {
                $q->whereHas('course', function ($q) use ($courseName) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["{$courseName}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["{$courseName}%"]);
                });
            })
            ->with(['course.departments:id,name,code'])
            ->get();
    }

    public function getInstructorAssignedCourses(
        string $instructorId,
        array $filters,
        ?Collection $instructorAssignedSemesters = null
    ) {
        $semesterId = $filters['semester_id'] ?? null;
        $courseName = $filters['course_name'] ?? null;

        return CourseSemester::whereHas('instructorAssignments', function ($q) use ($semesterId, $instructorAssignedSemesters, $instructorId) {
            $q->where('student_id', $instructorId);
            $q->when($semesterId || $instructorAssignedSemesters->isNotEmpty(), function ($q) use ($semesterId, $instructorAssignedSemesters) {
                $q->where('semester_id', $semesterId ?? $instructorAssignedSemesters->first()->id);
            });
        })
            ->when($courseName, function ($q) use ($courseName) {
                $q->whereHas('course', function ($q) use ($courseName) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["{$courseName}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["{$courseName}%"]);
                });
            })
            ->with(['course.departments:id,name,code'])
            ->get();
    }
}
