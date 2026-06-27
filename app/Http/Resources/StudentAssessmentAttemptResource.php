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
            'student' => new StudentResource($this->whenLoaded('student')),
            'assessmentResult' => new AssessmentResultResource($this->whenLoaded('assessmentResult')),
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
