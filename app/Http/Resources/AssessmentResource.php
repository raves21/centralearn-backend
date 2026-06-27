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
            'name' => $this->whenLoaded('chapterContent', fn() => $this->chapterContent->name),
            'description' => $this->whenLoaded('chapterContent', fn() => $this->chapterContent->description),
            'chapterName' => $this->whenLoaded('chapterContent', function () {
                return $this->chapterContent->chapter?->name ?? null;
            }),
            'submissionSettings' => new AssessmentSubmissionSettingsResource($this->whenLoaded('submissionSettings')),
            'maxAchievableScore' => $this->max_achievable_score,
            'isAnswersViewableAfterSubmit' => (bool) $this->is_answers_viewable_after_submit,
            'isScoreViewableAfterSubmit' => (bool) $this->is_score_viewable_after_submit,
            'maxAttempts' => $this->max_attempts,
            'multiAttemptGradingType' => $this->multi_attempt_grading_type,
            'createdAt' => (string) $this->created_at,
            'updatedAt' => (string) $this->updated_at
        ];
    }
}
