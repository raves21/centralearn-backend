<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function courseClass()
    {
        return $this->hasMany(CourseClass::class);
    }
}
