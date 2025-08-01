<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseStudentEnrollment extends Model
{
    protected $table = 'course_student_enrollment';

    protected $fillable = [
        'student_id',
        'course_semester_id',
        'final_grade'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseSemester()
    {
        return $this->belongsTo(CourseSemester::class);
    }
}
