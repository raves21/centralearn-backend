<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Semester extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function courseClasses()
    {
        return $this->hasMany(CourseClass::class);
    }
}
