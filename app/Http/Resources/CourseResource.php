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
            'instructors' => $this->whenLoaded('instructors', function () {
                return $this->instructors->map(function ($instructor) {
                    return [
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                    ];
                });
            }),
            'departments' => $this->whenLoaded('departments', function () {
                return $this->departments->map(fn($dept) => [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'code' => $dept->code
                ]);
            }),
            'imagePath' => $this->image_path,
            'description' => $this->description
        ];
    }
}
