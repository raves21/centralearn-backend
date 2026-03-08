<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentVersionResource extends JsonResource
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
            'assessmentId' => $this->assessment_id,
            'maxAchievableScore' => $this->max_achievable_score,
            'versionNumber' => $this->version_number,
            'questionnaireSnapshot' => $this->questionnaire_snapshot
        ];
    }
}
