<?php

namespace App\Http\Repositories;

use App\Models\ClassInstructorAssignment;

class ClassInstructorAssignmentRepository extends BaseRepository
{
    public function __construct(ClassInstructorAssignment $classInstructorAssignment)
    {
        parent::__construct($classInstructorAssignment);
    }

    public function checkClassAssignmentExistence(string $instructorId, string $classId)
    {
        $assignmentExists = ClassInstructorAssignment::where('instructor_id', $instructorId)
            ->where('course_class_id', $classId)->exists();

        return $assignmentExists;
    }
}
