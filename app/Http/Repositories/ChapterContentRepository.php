<?php

namespace App\Http\Repositories;

use App\Http\Resources\ChapterContentResource;
use App\Models\Assessment;
use App\Models\ChapterContent;
use App\Models\Lecture;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChapterContentRepository extends BaseRepository
{
    public function __construct(ChapterContent $chapterContent)
    {
        parent::__construct($chapterContent);
    }

    public function getAllLectures(string $chapterId)
    {

        return ChapterContent::where('contentable_type', Lecture::class)
            ->where('chapter_id', $chapterId)
            ->with('contentable.lectureMaterials.materialable')
            ->get();
    }
}
