<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LectureMaterialResource extends JsonResource
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
            'lectureId' => $this->lecture_id,
            'materialId' => $this->materialable_id,
            'materialType' => $this->materialable_type,
            'material' => $this->whenLoaded('materialable', fn() => $this->materialable->toArary())
        ];
    }
}
