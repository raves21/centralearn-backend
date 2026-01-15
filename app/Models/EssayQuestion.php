<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class EssayQuestion extends Model
{
    use HasUuids;

    protected $fillable = [
        'question_text',
        'point_worth'
    ];
}
