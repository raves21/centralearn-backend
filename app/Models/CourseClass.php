<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function studentEnrollments()
    {
        return $this->hasMany(ClassStudentEnrollment::class);
    }

    public function instructorAssignments()
    {
        return $this->hasMany(ClassInstructorAssignment::class);
    }

    public function instructors()
    {
        return $this->hasManyThrough(
            Instructor::class,
            ClassInstructorAssignment::class,
            'course_id',
            'id',
            'id',
            'instructor_id'
        );
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            ClassStudentEnrollment::class,
            'course_id',
            'id',
            'id',
            'student_id'
        );
    }
}
