<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Term extends Model
{
    use HasUuids;

    protected $fillable = ['course_id', 'title', 'description'];
}
