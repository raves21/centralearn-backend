<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'departments' => $this->whenLoaded('departments', function () {
                return $this->departments->map(fn($dept) => $dept->code);
            }),
            'imagePath' => $this->image_path,
            'description' => $this->description
        ];
    }
}
