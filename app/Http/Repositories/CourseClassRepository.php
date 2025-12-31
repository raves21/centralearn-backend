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

    public function getAll(
        array $relationships = [],
        array $filters = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = CourseClass::query();

        // Apply query filter (course name/code search)
        $query->when($filters['query'] ?? null, function ($q) use ($filters) {
            $q->whereHas('course', function ($q) use ($filters) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$filters['query']}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$filters['query']}%"]);
            });
        });

        // Apply course_id filter
        $query->when($filters['course_id'] ?? null, function ($q, $courseId) {
            $q->where('course_id', $courseId);
        });

        // Apply semester_id filter
        $query->when($filters['semester_id'] ?? null, function ($q, $semesterId) {
            $q->where('semester_id', $semesterId);
        });

        // Apply status filter
        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        });

        // Apply relationships
        if (!empty($relationships)) {
            $query->with($relationships);
        }

        // Apply ordering
        $query->orderBy($orderBy, $sortDirection);

        if ($paginate) {
            return $query->paginate();
        }

        return $query->get();
    }

    public function getStudentEnrolledClasses(
        string $studentId,
        array $filters,
        ?Collection $studentEnrolledSemesters = null
    ) {
        $semesterId = $filters['semester_id'] ?? null;
        $searchQueryFilter = $filters['query'] ?? null;
        $statusFilter = $filters['status'] ?? null;

        $paginateFilter = $filters['paginate'] ?? null;
        $shouldPaginate = $paginateFilter !== null ? $paginateFilter : true;

        $query = CourseClass::whereHas('studentEnrollments', function ($q) use ($semesterId, $studentEnrolledSemesters, $studentId, $statusFilter) {
            $q->where('student_id', $studentId);
            $q->when($semesterId || $studentEnrolledSemesters->isNotEmpty(), function ($q) use ($semesterId, $studentEnrolledSemesters) {
                $q->where('semester_id', $semesterId ?? $studentEnrolledSemesters->first()->id);
            });
        })
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($searchQueryFilter, function ($q) use ($searchQueryFilter) {
                $q->whereHas('course', function ($q) use ($searchQueryFilter) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchQueryFilter}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$searchQueryFilter}%"]);
                });
            })
            ->with(['course.departments:id,name,code', 'semester', 'section']);

        if ($shouldPaginate) return $query->paginate();
        return $query->get();
    }

    public function getInstructorAssignedClasses(
        string $instructorId,
        array $filters,
        ?Collection $instructorAssignedSemesters = null
    ) {
        $semesterId = $filters['semester_id'] ?? null;
        $searchQueryFilter = $filters['query'] ?? null;
        $statusFilter = $filters['status'] ?? null;

        $paginateFilter = $filters['paginate'] ?? null;
        $shouldPaginate = $paginateFilter !== null ? $paginateFilter : true;

        $query = CourseClass::whereHas('instructorAssignments', function ($q) use ($semesterId, $instructorAssignedSemesters, $instructorId) {
            $q->where('instructor_id', $instructorId);
            $q->when($semesterId || $instructorAssignedSemesters->isNotEmpty(), function ($q) use ($semesterId, $instructorAssignedSemesters) {
                $q->where('semester_id', $semesterId ?? $instructorAssignedSemesters->first()->id);
            });
        })
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($searchQueryFilter, function ($q) use ($searchQueryFilter) {
                $q->whereHas('course', function ($q) use ($searchQueryFilter) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchQueryFilter}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$searchQueryFilter}%"]);
                });
            })
            ->with(['course.departments:id,name,code', 'semester', 'section']);

        if ($shouldPaginate) return $query->paginate();
        return $query->get();
    }

    public function getStudentEnrollableClasses(Student $student, array $filters = [])
    {
        $enrolledClassesIds = $student->courseEnrollments()->pluck('course_class_id');

        $enrollableClasses = CourseClass::whereNotIn('id', $enrolledClassesIds)
            ->whereHas('course.departments', function ($q) use ($student) {
                $q->where('departments.id', $student->program->department_id);
            })
            ->when($filters['course_id'] ?? null, function ($q, $courseId) {
                $q->whereHas('course', function ($q) use ($courseId) {
                    $q->where('id', $courseId);
                });
            })
            ->when($filters['section_id'] ?? null, function ($q, $sectionId) {
                $q->whereHas('section', function ($q) use ($sectionId) {
                    $q->where('id', $sectionId);
                });
            })
            ->when($filters['semester_id'] ?? null, function ($q, $semesterId) {
                $q->whereHas('semester', function ($q) use ($semesterId) {
                    $q->where('id', $semesterId);
                });
            })
            ->with(['course', 'semester', 'section'])
            ->paginate();

        return $enrollableClasses;
    }

    public function getInstructorAssignableClasses(Instructor $instructor, array $filters = [])
    {
        $enrolledClassesIds = $instructor->courseAssignments()->pluck('course_class_id');

        $enrollableClasses = CourseClass::whereNotIn('id', $enrolledClassesIds)
            ->whereHas('course.departments', function ($q) use ($instructor) {
                $q->where('departments.id', $instructor->department_id);
            })
            ->when($filters['course_id'] ?? null, function ($q, $courseId) {
                $q->whereHas('course', function ($q) use ($courseId) {
                    $q->where('id', $courseId);
                });
            })
            ->when($filters['section_id'] ?? null, function ($q, $sectionId) {
                $q->whereHas('section', function ($q) use ($sectionId) {
                    $q->where('id', $sectionId);
                });
            })
            ->when($filters['semester_id'] ?? null, function ($q, $semesterId) {
                $q->whereHas('semester', function ($q) use ($semesterId) {
                    $q->where('id', $semesterId);
                });
            })
            ->with(['course', 'semester', 'section'])
            ->paginate();

        return $enrollableClasses;
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

    public function getChapterCount(CourseClass $courseClass)
    {
        return $courseClass->chapters()->count();
    }
}
