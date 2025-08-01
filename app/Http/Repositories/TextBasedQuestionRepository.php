<?php

namespace App\Http\Repositories;

use App\Models\TextBasedQuestion;

class TextBasedQuestionRepository extends BaseRepository
{
    public function __construct(TextBasedQuestion $textBasedQuestion)
    {
        parent::__construct($textBasedQuestion);
    }
}