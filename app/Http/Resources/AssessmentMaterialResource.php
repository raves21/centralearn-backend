<?php

namespace App\Http\Resources;

use App\Models\FileAttachment;
use App\Models\OptionBasedQuestion;
use App\Models\TextAttachment;
use App\Models\TextBasedQuestion;
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
            'assessmentId' => $this->assessment_id,
            'order' => $this->order,
            'materialType' => $this->materialable_type,
            'materialId' => $this->materialable_id,
            'material' => $this->whenLoaded('materialable', function () {
                $material = $this->materialable;
                switch ($this->materialable_type) {
                    case TextAttachment::class:
                        return new TextAttachmentResource($material);
                    case FileAttachment::class:
                        return new FileAttachmentResource($material);
                    case OptionBasedQuestion::class:
                        return new OptionBasedQuestionResource($material);
                    case TextBasedQuestion::class:
                        return new TextBasedQuestionResource($material);
                }
            })
        ];
    }
}
