<?php

namespace App\Http\Resources;

use App\Models\Assessment;
use App\Models\Lecture;
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
            'isOpen' => (bool)$this->is_open,
            'opensAt' => $this->opens_at,
            'closesAt' => $this->closes_at,
            'chapterId' => $this->chapter_id,
            'isPublished' => (bool)$this->is_published,
            'publishesAt' => $this->publishes_at,
            'order' => $this->order,
            'contentId' => $this->contentable_id,
            'contentType' => $this->contentable_type,
            'content' => $this->whenLoaded('contentable', function () {
                $content = $this->contentable;
                switch ($this->contentable_type) {
                    case Lecture::class:
                        return new LectureResource($content);
                    case Assessment::class:
                        return new AssessmentResource($content);
                }
            })
        ];
    }
}
