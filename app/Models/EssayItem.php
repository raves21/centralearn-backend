<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class EssayItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'point_worth',
        'min_character_count',
        'max_character_count',
        'min_word_count',
        'max_word_count',
    ];
}
