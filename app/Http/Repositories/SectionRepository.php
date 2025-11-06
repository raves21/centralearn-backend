<?php

namespace App\Http\Repositories;

use App\Models\Section;

class SectionRepository extends BaseRepository
{
    public function __construct(Section $section)
    {
        parent::__construct($section);
    }
}
