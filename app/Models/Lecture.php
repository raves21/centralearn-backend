<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Lecture extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function chapterContent()
    {
        return $this->morphOne(ChapterContent::class, 'contentable');
    }

    public function lectureMaterials()
    {
        return $this->hasMany(LectureMaterial::class);
    }
}
