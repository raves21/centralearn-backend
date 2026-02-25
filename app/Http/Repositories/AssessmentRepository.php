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
        $total = 0;

        foreach ($assessment->assessmentMaterials as $assessmentMaterial) {
            $total += $assessmentMaterial->point_worth;
        }

        $assessment->update([
            'max_achievable_score' => $total
        ]);

        return $total;
    }
}
