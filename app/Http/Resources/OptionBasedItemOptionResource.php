<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionBasedItemOptionResource extends JsonResource
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
            'optionBasedItemId' => $this->option_based_item_id,
            'order' => $this->order,
            'optionText' => $this->option_text,
            'optionFile' => $this->option_file,
            'isCorrect' => (bool) $this->is_correct,
        ];
    }
}
