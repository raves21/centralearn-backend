<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudentEnrollment extends Model
{
    protected $table = 'class_student_enrollment';

    protected $fillable = [
        'student_id',
        'course_class_id',
        'final_grade'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
