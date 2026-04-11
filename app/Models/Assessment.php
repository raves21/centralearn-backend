<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Assessment extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'submission_settings' => 'array'
    ];

    protected $with = [
        'submissionSettings'
    ];

    public function chapterContent()
    {
        return $this->morphOne(ChapterContent::class, 'contentable');
    }

    public function assessmentMaterials()
    {
        return $this->hasMany(AssessmentMaterial::class);
    }

    public function assessmentVersions()
    {
        return $this->hasMany(AssessmentVersion::class);
    }

    public function assessmentResults()
    {
        return $this->hasMany(AssessmentResult::class);
    }

    public function submissionSettings()
    {
        return $this->hasOne(AssessmentSubmissionSettings::class);
    }
}
