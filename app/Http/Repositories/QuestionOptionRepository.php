<?php

namespace App\Http\Repositories;

use App\Models\QuestionOption;

class QuestionOptionRepository extends BaseRepository
{
    public function __construct(QuestionOption $questionOption)
    {
        parent::__construct($questionOption);
    }
}