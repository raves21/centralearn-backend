<?php

namespace App\Http\Resources;

use App\Models\Assessment;
use App\Models\Lecture;
use Carbon\Carbon;
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
            'isOpen' => $this->opens_at ? Carbon::parse($this->opens_at)->lessThanOrEqualTo(now()) : false,
            'opensAt' => $this->opens_at,
            'closesAt' => $this->closes_at,
            'chapter' => $this->whenLoaded('chapter', fn() => new ChapterResource($this->chapter)),
            'isPublished' => $this->publishes_at ? Carbon::parse($this->publishes_at)->lessThanOrEqualTo(now()) : false,
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
