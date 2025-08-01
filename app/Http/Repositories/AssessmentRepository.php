<?php

namespace App\Http\Repositories;

use App\Models\Assessment;

class AssessmentRepository extends BaseRepository
{
    public function __construct(Assessment $assessment)
    {
        parent::__construct($assessment);
    }
}
