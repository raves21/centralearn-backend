<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AssessmentVersion extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'questionnaire_snapshot' => 'array',
        'answer_key' => 'array'
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
