<?php

namespace App\Http\Repositories;

use App\Models\AssessmentResult;

class AssessmentResultRepository extends BaseRepository
{
    public function __construct(AssessmentResult $assessmentResult)
    {
        parent::__construct($assessmentResult);
    }
}