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
        'publishes_at',
        'contentable_id',
        'contentable_type',
        'opens_at',
        'closes_at',
        'description',
        'order'
    ];

    protected $with = ['contentable'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function contentable()
    {
        return $this->morphTo();
    }
}
