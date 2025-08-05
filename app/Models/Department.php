<?php

namespace App\Models;

use App\Observers\DepartmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Department extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'image_url',
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
