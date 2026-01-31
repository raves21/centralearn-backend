<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Assessment extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

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
