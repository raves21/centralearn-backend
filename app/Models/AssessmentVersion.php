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
}
