<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionBasedItemResource extends JsonResource
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
            'pointWorth' => $this->point_worth,
            'options' => $this->whenLoaded('options', fn() => OptionBasedItemOptionResource::collection($this->options))
        ];
    }
}
