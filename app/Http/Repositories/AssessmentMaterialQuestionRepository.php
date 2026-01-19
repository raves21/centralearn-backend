<?php

namespace App\Http\Repositories;

use App\Models\AssessmentMaterialQuestion;

class AssessmentMaterialQuestionRepository extends BaseRepository
{
    public function __construct(AssessmentMaterialQuestion $assessmentMaterialQuestion)
    {
        parent::__construct($assessmentMaterialQuestion);
    }
}