<?php

namespace App\Http\Resources;

use App\Models\FileAttachment;
use App\Models\TextAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionResource extends JsonResource
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
            'optionBasedQuestionId' => $this->option_based_question_id,
            'order' => $this->order,
            'isCorrect' => (bool) $this->is_correct,
            'optionType' => $this->optionable_type,
            'optionId' => $this->optionable_id,
            'option' => $this->whenLoaded('optionable', function () {
                $option = $this->optionable;
                switch ($this->optionable_type) {
                    case TextAttachment::class:
                        return new TextAttachmentResource($option);
                    case FileAttachment::class;
                        return new FileAttachmentResource($option);
                }
            })
        ];
    }
}
