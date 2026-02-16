<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timeLimit' => $this->time_limit,
            'maxAchievableScore' => $this->max_achievable_score,
            'isAnswersViewableAfterSubmit' => (bool) $this->is_answers_viewable_after_submit,
            'isScoreViewableAfterSubmit' => (bool) $this->is_score_viewable_after_submit,
            'isMultiAttempts' => (bool) $this->is_multi_attempts,
            'maxAttempts' => $this->max_attempts,
            'multiAttemptGradingType' => $this->multi_attempt_grading_type
        ];
    }
}
