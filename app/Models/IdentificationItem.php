<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentificationItem extends Model
{
    protected $fillable = [
        'point_worth',
        'accepted_answers'
    ];

    protected $casts = [
        'accepted_answers' => 'json'
    ];
}
