<?php

namespace App\Http\Resources;

use App\Models\FileAttachment;
use App\Models\TextAttachment;
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
            'order' => (int)$this->order,
            'materialId' => $this->materialable_id,
            'materialType' => $this->materialable_type,
            'material' => $this->whenLoaded('materialable', function () {
                $material = $this->materialable;
                switch ($this->materialable_type) {
                    case TextAttachment::class:
                        return new TextAttachmentResource($material);
                    case FileAttachment::class:
                        return new FileAttachmentResource($material);
                }
            })
        ];
    }
}
