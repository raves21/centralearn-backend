<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class FileAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'path',
        'type',
        'extension',
        'name',
        'mime',
        'size'
    ];

    public function lectureMaterial()
    {
        return $this->morphOne(LectureMaterial::class, 'materialable');
    }

    public function assessmentMaterial()
    {
        return $this->morphOne(AssessmentMaterial::class, 'materialable');
    }

    public function questionOption()
    {
        return $this->morphOne(QuestionOption::class, 'optionable');
    }
}
