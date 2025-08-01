<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentMaterialResource extends JsonResource
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
            'assessmentId' => $this->material_id,
            'materialType' => $this->materialable_type,
            'materialId' => $this->materialable_id,
            'material' => $this->whenLoaded('materialable', fn() => $this->materialable->toArray())
        ];
    }
}
