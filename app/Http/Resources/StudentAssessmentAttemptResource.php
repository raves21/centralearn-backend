<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAssessmentAttemptResource extends JsonResource
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
            'studentId' => $this->student_id,
            'assessmentVersion' => new AssessmentVersionResource($this->whenLoaded('assessmentVersion')),
            'attemptNumber' => $this->attempt_number,
            'answers' => $this->answers,
            'submissionSummary' => $this->submission_summary,
            'status' => $this->status,
            'startedAt' => $this->started_at,
            'submittedAt' => $this->submitted_at,
            'totalScore' => $this->total_score
        ];
    }
}
