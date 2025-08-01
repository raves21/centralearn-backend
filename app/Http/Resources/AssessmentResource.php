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
            'isOpen' => $this->is_open,
            'opensAt' => $this->opens_at,
            'closesAt' => $this->closes_at,
            'timeLimit' => $this->time_limit,
            'maxScore' => $this->max_score,
            'isAnswersViewableAfterSubmit' => $this->is_answers_viewable_after_submit,
            'isScoreViewableAfterSubmit' => $this->is_score_viewable_after_submit,
            'isMultiAttempts' => $this->is_multi_attempts,
            'maxAttempts' => $this->max_attempts,
            'multiAttemptGradingType' => $this->multi_attempt_grading_type
        ];
    }
}
