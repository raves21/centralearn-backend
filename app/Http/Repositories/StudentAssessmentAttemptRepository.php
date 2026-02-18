<?php

namespace App\Http\Repositories;

use App\Models\StudentAssessmentAttempt;

class StudentAssessmentAttemptRepository extends BaseRepository
{
    public function __construct(StudentAssessmentAttempt $studentAssessmentAttempt)
    {
        parent::__construct($studentAssessmentAttempt);
    }
}