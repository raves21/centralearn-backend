<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Student extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function assessmentSubmissions()
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function courseEnrollments()
    {
        return $this->hasMany(ClassStudentEnrollment::class);
    }
}
