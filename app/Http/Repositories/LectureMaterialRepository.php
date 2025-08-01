<?php

namespace App\Http\Repositories;

use App\Models\LectureMaterial;

class LectureMaterialRepository extends BaseRepository
{
    public function __construct(LectureMaterial $lectureMaterial)
    {
        parent::__construct($lectureMaterial);
    }
}
