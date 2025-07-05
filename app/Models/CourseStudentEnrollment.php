<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use PDO;

class CourseStudentEnrollment extends Model
{
    use HasUuids;

    protected $table = 'course_student';

    protected $fillable = [
        'student_id',
        'course_id',
        'semester_id',
        'final_grade'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
