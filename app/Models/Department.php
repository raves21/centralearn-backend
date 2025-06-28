<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Department extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'code'
    ];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
}
