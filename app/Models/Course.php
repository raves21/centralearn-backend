<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Course extends Model
{
    use HasUuids;

    protected $fillable = ['description', 'code', 'image_path', 'name'];

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function courseSemesters()
    {
        return $this->hasMany(CourseSemester::class);
    }
}
