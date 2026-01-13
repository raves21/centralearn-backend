<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentRepository;

class AssessmentService
{
    private $assessmentRepo;

    public function __construct(AssessmentRepository $assessmentRepo)
    {
        $this->assessmentRepo = $assessmentRepo;
    }
}
