<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TextBasedQuestionResource extends JsonResource
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
            'questionText' => $this->question_text,
            'pointWorth' => $this->point_worth,
            'type' => $this->type,
            'identificationAnswer' => $this->identification_answer,
            'isIdentificationAnswerCaseSensitive' => $this->is_identification_answer_case_sensitive,
        ];
    }
}
