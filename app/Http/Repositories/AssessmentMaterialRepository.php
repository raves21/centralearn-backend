<?php

namespace App\Http\Repositories;

use App\Models\AssessmentMaterial;

class AssessmentMaterialRepository extends BaseRepository
{
    public function __construct(AssessmentMaterial $assessmentMaterial)
    {
        parent::__construct($assessmentMaterial);
    }
}