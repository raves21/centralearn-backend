<?php

namespace App\Http\Repositories;

use App\Http\Requests\ClassStudentEnrollment\GetStudentCourses;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentRepository extends BaseRepository
{
    public function __construct(Student $student)
    {
        parent::__construct($student);
    }

    public function currentUserStudentProfile()
    {
        $student = Student::where('user_id', Auth::user()->id)->firstOrFail();
        return $student->load([
            'program:id,name,code,department_id',
            'program.department:id,name,code'
        ]);
    }
}
