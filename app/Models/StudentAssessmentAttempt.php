<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StudentAssessmentAttempt extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'answers' => 'array',
        'submission_summary' => 'array'
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
