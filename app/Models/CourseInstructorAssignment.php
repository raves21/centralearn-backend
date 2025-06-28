<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CourseInstructorAssignment extends Model
{
    use HasUuids;

    protected $table = 'course_instructor';

    protected $fillable = [
        'instructor_id',
        'course_id',
        'term_id'
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
