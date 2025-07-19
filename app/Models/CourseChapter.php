<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class CourseChapter extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_id',
        'name',
        'description',
        'order',
        'published_at'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function contents()
    {
        return $this->hasMany(ChapterContent::class);
    }
}
