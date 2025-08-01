<?php

namespace App\Http\Repositories;

use App\Models\OptionBasedQuestion;

class OptionBasedQuestionRepository extends BaseRepository
{
    public function __construct(OptionBasedQuestion $optionBasedQuestion)
    {
        parent::__construct($optionBasedQuestion);
    }
}