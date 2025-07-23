<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class ChapterContent extends Model
{
    use HasUuids;

    protected $fillable = [
        'chapter_id',
        'name',
        'is_published',
        'publishes_at',
        'contentable_id',
        'contentable_type',
        'order'
    ];

    protected $with = ['contentable'];

    public function chapter()
    {
        return $this->belongsTo(CourseChapter::class);
    }

    public function contentable()
    {
        return $this->morphTo();
    }
}
