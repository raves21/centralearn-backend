<?php

namespace App\Http\Repositories;

use App\Models\Assessment;

class AssessmentRepository extends BaseRepository
{
    public function __construct(Assessment $assessment)
    {
        parent::__construct($assessment);
    }

    public function updateMaxAchievableScore(Assessment $assessment)
    {
        $totalPoints = $assessment->assessmentMaterials->pluck('point_worth')->sum();

        $assessment->update([
            'max_achievable_score' => $totalPoints
        ]);
    }
}
