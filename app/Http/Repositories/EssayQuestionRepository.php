<?php

namespace App\Http\Repositories;

use App\Models\EssayQuestion;

class EssayQuestionRepository extends BaseRepository
{
    public function __construct(EssayQuestion $essayQuestion)
    {
        parent::__construct($essayQuestion);
    }
}
