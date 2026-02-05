<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassInstructorAssignment extends Model
{
    protected $table = 'class_instructor_assignment';

    protected $guarded = ['id'];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
