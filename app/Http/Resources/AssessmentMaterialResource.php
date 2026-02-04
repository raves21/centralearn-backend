<?php

namespace App\Http\Resources;

use App\Models\FileAttachment;
use App\Models\TextAttachment;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;
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
            'question' => $this->whenLoaded('assessmentMaterialQuestion', fn() => new AssessmentMaterialQuestionResource($this->assessmentMaterialQuestion)),
            'pointWorth' => $this->point_worth,
            'material' => $this->whenLoaded('materialable', function () {
                $material = $this->materialable;
                switch ($this->materialable_type) {
                    case OptionBasedItem::class:
                        return new OptionBasedItemResource($material);
                    case EssayItem::class:
                        return new EssayItemResource($material);
                    case IdentificationItem::class:
                        return new IdentificationItemResource($material);
                }
            })
        ];
    }
}
