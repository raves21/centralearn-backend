<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Chapter extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function contents()
    {
        return $this->hasMany(ChapterContent::class);
    }
}
