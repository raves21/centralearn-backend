<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CourseInstructorAssignment extends Model
{
    protected $table = 'course_instructor_assignment';

    protected $fillable = [
        'instructor_id',
        'course_semester_id',
        'semester_id'
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
