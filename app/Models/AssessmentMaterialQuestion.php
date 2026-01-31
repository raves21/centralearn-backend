<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentMaterialQuestion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'question_files' => 'array',
    ];
}
