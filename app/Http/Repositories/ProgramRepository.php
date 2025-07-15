<?php

namespace App\Http\Repositories;

use App\Models\Program;

class ProgramRepository extends BaseRepository
{
    public function __construct(Program $program)
    {
        parent::__construct($program);
    }
}
