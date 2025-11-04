<?php

namespace App\Http\Repositories;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Instructor;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Collection;

class CourseClassRepository extends BaseRepository
{
    public function __construct(CourseClass $courseClass)
    {
        parent::__construct($courseClass);
    }

    public function getStudentEnrolledClasses(
        string $studentId,
        array $filters,
        ?Collection $studentEnrolledSemesters = null
    ) {
        $semesterId = $filters['semester_id'] ?? null;
        $searchQueryFilter = $filters['query'] ?? null;

        $paginate = $filters['paginate'] ?? false;

        $query = CourseClass::whereHas('studentEnrollments', function ($q) use ($semesterId, $studentEnrolledSemesters, $studentId) {
            $q->where('student_id', $studentId);
            $q->when($semesterId || $studentEnrolledSemesters->isNotEmpty(), function ($q) use ($semesterId, $studentEnrolledSemesters) {
                $q->where('semester_id', $semesterId ?? $studentEnrolledSemesters->first()->id);
            });
        })
            ->when($searchQueryFilter, function ($q) use ($searchQueryFilter) {
                $q->whereHas('course', function ($q) use ($searchQueryFilter) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchQueryFilter}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$searchQueryFilter}%"]);
                });
            })
            ->with(['course.departments:id,name,code', 'semester']);

        if ($paginate) return $query->paginate();
        return $query->get();
    }

    public function getInstructorAssignedClasses(
        string $instructorId,
        array $filters,
        ?Collection $instructorAssignedSemesters = null
    ) {
        $semesterId = $filters['semester_id'] ?? null;
        $searchQueryFilter = $filters['query'] ?? null;

        return CourseClass::whereHas('instructorAssignments', function ($q) use ($semesterId, $instructorAssignedSemesters, $instructorId) {
            $q->where('instructor_id', $instructorId);
            $q->when($semesterId || $instructorAssignedSemesters->isNotEmpty(), function ($q) use ($semesterId, $instructorAssignedSemesters) {
                $q->where('semester_id', $semesterId ?? $instructorAssignedSemesters->first()->id);
            });
        })
            ->when($searchQueryFilter, function ($q) use ($searchQueryFilter) {
                $q->whereHas('course', function ($q) use ($searchQueryFilter) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchQueryFilter}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$searchQueryFilter}%"]);
                });
            })
            ->with(['course.departments:id,name,code', 'semester'])
            ->get();
    }

    public function getStudentEnrollableClasses(Student $student, Semester $semester)
    {

        $enrolledClassesIds = $student->courseEnrollments()
            ->whereHas('courseClass', function ($q) use ($semester) {
                $q->where('semester_id', $semester->id);
            })
            ->pluck('course_class_id');

        $enrollableClasses = CourseClass::whereNotIn('id', $enrolledClassesIds)
            ->whereHas('course.departments', function ($q) use ($student) {
                $q->where('departments.id', $student->program->department_id);
            })
            ->with(['course', 'semester'])
            ->get();

        return $enrollableClasses;
    }

    public function getInstructorAssignableClasses(Instructor $instructor, Semester $semester)
    {
        $assignedClassesIds = $instructor->courseAssignments()
            ->whereHas('courseClass', function ($q) use ($semester) {
                $q->where('semester_id', $semester->id);
            })
            ->pluck('course_class_id');

        $assignableClasses = CourseClass::whereNotIn('id', $assignedClassesIds)
            ->whereHas('course.departments', function ($q) use ($instructor) {
                $q->where('departments.id', $instructor->department_id);
            })
            ->with(['course', 'semester'])
            ->get();

        return $assignableClasses;
    }

    public function verifyStudentDepartment(Student $student, string $classId)
    {
        $studentInClassDepartment = CourseClass::where('id', $classId)
            ->whereHas('course.departments', function ($q) use ($student) {
                $q->where('departments.id', $student->program->department_id);
            })->exists();

        if (!$studentInClassDepartment) abort(409, 'Student must belong in the class\'s departments.');
    }

    public function verifyInstructorDepartment(Instructor $instructor, string $classId)
    {
        $instructorInClassDepartment = CourseClass::where('id', $classId)
            ->whereHas('course.departments', function ($q) use ($instructor) {
                $q->where('departments.id', $instructor->department_id);
            })->exists();

        if (!$instructorInClassDepartment) abort(409, 'Instructor must belong in the class\'s departments.');
    }
}
