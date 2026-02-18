<?php

namespace App\Http\Repositories;

use App\Models\AssessmentVersion;

class AssessmentVersionRepository extends BaseRepository
{
    public function __construct(AssessmentVersion $assessmentVersion)
    {
        parent::__construct($assessmentVersion);
    }
}