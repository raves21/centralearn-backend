<?php

namespace App\Http\Repositories;

use App\Models\AssessmentVersion;

class AssessmentVersionRepository extends BaseRepository
{
    public function __construct(AssessmentVersion $assessmentVersion)
    {
        parent::__construct($assessmentVersion);
    }

    public function getLatestAssessmentVersion(string $assessmentId)
    {
        return AssessmentVersion::whereHas('assessment', fn($q) => $q->where('id', $assessmentId))
            ->latest('version_number')
            ->pluck('version_number');
    }
}
