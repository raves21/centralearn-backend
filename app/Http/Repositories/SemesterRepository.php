<?php

namespace App\Http\Repositories;

use App\Models\Semester;

class SemesterRepository extends BaseRepository
{

    public function __construct(Semester $semester)
    {
        parent::__construct($semester);
    }
}
