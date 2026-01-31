<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudentEnrollment extends Model
{
    protected $table = 'class_student_enrollment';

    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
