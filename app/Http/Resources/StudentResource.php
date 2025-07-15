<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'program' => $this->whenLoaded('program', function () {
                return [
                    'id' => $this->program->id,
                    'name' => $this->program->name,
                    'code' => $this->program->code
                ];
            }),
            'department' => $this->whenLoaded('program', function () {
                $department = optional($this->program->department);
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'code' => $department->code
                ];
            })
        ];
    }
}
