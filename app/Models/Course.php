<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Course extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function courseClasses()
    {
        return $this->hasMany(CourseClass::class);
    }
}
