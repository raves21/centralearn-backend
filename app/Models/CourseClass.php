<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasUuids;

    protected $fillable = ['course_id', 'semester_id', 'section_name', 'status', 'image_url'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function studentEnrollments()
    {
        return $this->hasMany(CourseStudentEnrollment::class);
    }

    public function instructorAssignments()
    {
        return $this->hasMany(CourseInstructorAssignment::class);
    }

    public function instructors()
    {
        return $this->hasManyThrough(
            Instructor::class,
            CourseInstructorAssignment::class,
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
            CourseStudentEnrollment::class,
            'course_id',
            'id',
            'id',
            'student_id'
        );
    }
}
