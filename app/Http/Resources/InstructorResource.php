<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
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
            'user' => new UserResource($this->user),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'jobTitle' => $this->job_title,
            'isAdmin' => (bool) $this->user->hasRole(Role::ADMIN)
        ];
    }
}
