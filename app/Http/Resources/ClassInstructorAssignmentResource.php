<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassInstructorAssignmentResource extends JsonResource
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
            'courseClass' => new CourseClassResource($this->whenLoaded('courseClass')),
            'instructor' => new InstructorResource($this->whenLoaded('instructor')),
        ];
    }
}
