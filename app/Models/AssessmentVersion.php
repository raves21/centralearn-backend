<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'questionnaire',
        'answer_key'
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function studentAssessmentAttempts()
    {
        return $this->hasMany(StudentAssessmentAttempt::class);
    }
}
