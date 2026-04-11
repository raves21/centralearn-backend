<?php

namespace App\Http\Repositories;

use App\Models\AssessmentSubmissionSettings;
use App\Http\Repositories\BaseRepository;

class AssessmentSubmissionSettingsRepository extends BaseRepository
{
    public function __construct(AssessmentSubmissionSettings $assessmentSubmissionSettings)
    {
        parent::__construct($assessmentSubmissionSettings);
    }
}
