<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentificationItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'accepted_answers' => 'json'
    ];
}
