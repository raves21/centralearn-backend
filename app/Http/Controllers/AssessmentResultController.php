<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssessmentResult\GetResultAndAttempts;
use App\Http\Services\AssessmentResultService;
use Illuminate\Http\Request;

class AssessmentResultController extends Controller
{
    public function __construct(private AssessmentResultService $assessmentResultService) {}

    public function getResultAndAttempts(GetResultAndAttempts $request)
    {
        $validated = $request->validated();
        return $this->assessmentResultService->getResultAndAttempts($validated['student_id'], $validated['assessment_id']);
    }
}
