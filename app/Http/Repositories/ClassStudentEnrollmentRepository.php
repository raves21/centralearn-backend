<?php

namespace App\Http\Repositories;

use App\Models\ClassStudentEnrollment;

class ClassStudentEnrollmentRepository extends BaseRepository
{
    public function __construct(ClassStudentEnrollment $classStudentEnrollment)
    {
        parent::__construct($classStudentEnrollment);
    }

    public function checkStudentEnrollmentExistence(string $studentId, string $classId)
    {
        $enrollmentExists = ClassStudentEnrollment::where('student_id', $studentId)
            ->where('course_class_id', $classId)->exists();

        return $enrollmentExists;
    }
}
