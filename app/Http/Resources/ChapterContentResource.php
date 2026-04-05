<?php

namespace App\Http\Resources;

use App\Http\Services\ChapterContentService;
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
            'accessibilitySettings' => $this->accessibility_settings,
            'isAccessible' => ChapterContentService::isAccessible($this->id),
            'chapter' => $this->whenLoaded('chapter', fn() => new ChapterResource($this->chapter)),
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
