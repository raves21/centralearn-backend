<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class TextBasedQuestion extends Model
{
    use HasUuids;

    protected $fillable = [
        'question_text',
        'type',
        'identification_answer',
        'is_identification_answer_case_sensitive',
        'point_worth'
    ];
}
