<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class ChapterContent extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'accessibility_settings' => 'array',
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
