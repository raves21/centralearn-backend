<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAssessmentAttempt extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'answers',
        'submission_summary'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assessmentVersion()
    {
        return $this->belongsTo(AssessmentVersion::class);
    }
}
