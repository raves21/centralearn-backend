<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Student extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'program_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function assessmentSubmissions()
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function courseEnrollments()
    {
        return $this->hasMany(CourseStudentEnrollment::class);
    }
}
