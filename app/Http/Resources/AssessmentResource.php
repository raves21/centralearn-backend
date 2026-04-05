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
            'submissionSettings' => $this->submission_settings,
            'chapterContent' => new ChapterContentResource($this->whenLoaded('chapterContent')),
            'maxAchievableScore' => $this->max_achievable_score,
            'isAnswersViewableAfterSubmit' => (bool) $this->is_answers_viewable_after_submit,
            'isScoreViewableAfterSubmit' => (bool) $this->is_score_viewable_after_submit,
            'maxAttempts' => $this->max_attempts,
            'multiAttemptGradingType' => $this->multi_attempt_grading_type,
            'submissionSettings' => $this->submission_settings,
        ];
    }
}
