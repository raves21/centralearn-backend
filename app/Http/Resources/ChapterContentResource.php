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
            'name' => $this->name,
            'description' => $this->description,
            'isOpen' => $this->is_open,
            'opensAt' => $this->opens_at,
            'closesAt' => $this->closes_at,
            'chapterId' => $this->chapter_id,
            'isPublished' => $this->is_published,
            'publishesAt' => $this->publishes_at,
            'order' => $this->order,
            'contentId' => $this->contentable_id,
            'contentType' => $this->contentable_type,
            'content' => $this->whenLoaded('contentable', fn() => $this->contentable->toArray())
        ];
    }
}
