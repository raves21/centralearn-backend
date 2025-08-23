<?php

namespace App\Http\Repositories;

use App\Models\AssessmentMaterial;
use Illuminate\Support\Arr;

class AssessmentMaterialRepository extends BaseRepository
{
    public function __construct(AssessmentMaterial $assessmentMaterial)
    {
        parent::__construct($assessmentMaterial);
    }
}
