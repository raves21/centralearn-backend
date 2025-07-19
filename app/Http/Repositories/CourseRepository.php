<?php

namespace App\Http\Repositories;

use App\Models\Course;
use Illuminate\Support\Collection;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $course)
    {
        parent::__construct($course);
    }

    // public function getAll(array $filters){
    //     return 
    // }

    public function getStudentEnrolledCourses(
        string $studentId,
        array $filters,
        ?Collection $studentEnrolledSemesters = null
    ) {
        $semesterId = isset($filters['semester_id']) ? $filters['semester_id'] : null;
        $courseName = isset($filters['course_name']) ? $filters['course_name'] : null;

        return Course::whereHas('studentEnrollments', function ($q) use ($semesterId, $studentEnrolledSemesters, $studentId) {
            $q->where('student_id', $studentId);
            $q->when(isset($semesterId) || !$studentEnrolledSemesters->isEmpty(), function ($q) use ($semesterId, $studentEnrolledSemesters) {
                $q->where('semester_id', $semesterId ?: $studentEnrolledSemesters->first()->id);
            });
        })
            ->when(isset($courseName), function ($q) use ($courseName) {
                $q->where('name', 'LIKE', "%{$courseName}%")
                    ->orWhere('code', 'LIKE', "%{$courseName}%");
            })
            ->with(['departments:id,name,code', 'instructorAssignments:'])
            ->get();
    }

    public function getInstructorAssignedCourses(
        string $instructorId,
        array $filters,
        ?Collection $instructorAssignedSemesters = null
    ) {
        $semesterId = isset($filters['semester_id']) ? $filters['semester_id'] : null;
        $courseName = isset($filters['course_name']) ? $filters['course_name'] : null;

        return Course::whereHas('instructorAssignments', function ($q) use ($semesterId, $instructorAssignedSemesters, $instructorId) {
            $q->where('instructor_id', $instructorId);
            $q->when(isset($semesterId) || !$instructorAssignedSemesters->isEmpty(), function ($q) use ($semesterId, $instructorAssignedSemesters) {
                $q->where('semester_id', $semesterId ?: $instructorAssignedSemesters->first()->id);
            });
        })
            ->when(isset($courseName), function ($q) use ($courseName) {
                $q
                    ->where('name', 'LIKE', "%{$courseName}%")
                    ->orWhere('code', 'LIKE', "%{$courseName}%");
            })
            ->with(['departments:id,name,code'])
            ->get();
    }
}
