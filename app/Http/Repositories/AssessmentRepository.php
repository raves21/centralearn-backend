<?php

namespace App\Http\Repositories;

use App\Models\Assessment;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;

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

    public function buildAnswerKey(Assessment $assessment)
    {
        $answerKey = [];
        $assmtMaterialsSortedByOrder = $assessment->assessmentMaterials->sortBy('order')->values();

        foreach ($assmtMaterialsSortedByOrder as $assessmentMaterial) {
            switch ($assessmentMaterial->materialable_type) {
                case OptionBasedItem::class:
                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                        'correct_answer' => collect($assessmentMaterial->materialable->optionBasedItemOptions)->firstWhere('is_correct', true)->id
                    ];
                    break;
                case EssayItem::class:
                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                    ];
                    break;
                case IdentificationItem::class:
                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                        'accepted_answers' => $assessmentMaterial->materialable->accepted_answers
                    ];
                    break;
            }
        }

        return $answerKey;
    }
}
