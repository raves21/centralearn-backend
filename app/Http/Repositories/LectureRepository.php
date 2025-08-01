<?php

namespace App\Http\Repositories;

use App\Models\Lecture;

class LectureRepository extends BaseRepository
{
    public function __construct(Lecture $lecture)
    {
        parent::__construct($lecture);
    }
}
