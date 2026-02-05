<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IdentificationItem extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    protected $casts = [
        'accepted_answers' => 'json'
    ];
}
