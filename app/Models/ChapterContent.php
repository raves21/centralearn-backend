<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class ChapterContent extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'publishes_at' => 'datetime',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
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
