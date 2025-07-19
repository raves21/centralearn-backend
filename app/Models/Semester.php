<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Semester extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'start_date', 'end_date'];

    public function courseStudentEnrollments()
    {
        return $this->hasMany(CourseStudentEnrollment::class);
    }

    public function courseInstructorAssignments()
    {
        return $this->hasMany(CourseInstructorAssignment::class);
    }
}
