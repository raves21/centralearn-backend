<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PDO;

class Instructor extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'department_id', 'job_title'];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseInstructorAssignment::class);
    }
}
