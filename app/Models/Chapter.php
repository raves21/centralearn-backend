<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Chapter extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_id',
        'name',
        'description',
        'order',
        'published_at',
        'course_semester_id'
    ];

    public function courseSemester()
    {
        return $this->belongsTo(CourseSemester::class);
    }

    public function contents()
    {
        return $this->hasMany(ChapterContent::class);
    }
}
