<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentSubmissionSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'dueDate' => $this->due_date,
            'timeLimitSeconds' => $this->time_limit_seconds,
            'afterDueDateBehavior' => $this->after_due_date_behavior
        ];
    }
}
