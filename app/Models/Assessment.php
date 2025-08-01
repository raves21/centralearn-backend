<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Assessment extends Model
{
    use HasUuids;

    protected $fillable = [
        'is_open',
        'opens_at',
        'closes_at',
        'time_limit',
        'max_score',
        'is_answers_viewable_after_submit',
        'is_score_viewable_after_submit',
        'is_multi_attempts',
        'max_attempts',
        'multi_attempt_grading_type'
    ];

    public function submissions()
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function chapterContent()
    {
        return $this->morphOne(ChapterContent::class, 'contentable');
    }

    public function assessmentMaterials()
    {
        return $this->hasMany(AssessmentMaterial::class);
    }
}
