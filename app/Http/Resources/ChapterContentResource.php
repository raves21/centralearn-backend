<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterContentResource extends JsonResource
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
            'chapterId' => $this->chapter_id,
            'isPublished' => $this->is_published,
            'publishesAt' => $this->publishes_at,
            'order' => $this->order,
            'content' => $this->whenLoaded('contentable', function () {
                return $this->contentable->toArray();
            })
        ];
    }
}
