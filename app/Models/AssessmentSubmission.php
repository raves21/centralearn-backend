<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class AssessmentSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'assessment_id',
        'attempt_number',
        'answers_json',
        'submitted_at',
        'total_score',
        'graded_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
