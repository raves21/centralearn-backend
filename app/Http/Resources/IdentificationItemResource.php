<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdentificationItemResource extends JsonResource
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
            'acceptedAnswers' => $this->accepted_answers,
            'pointWorth' => $this->point_worth,
            'isCaseSensitive' => $this->is_case_sensitive,
        ];
    }
}
