<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentAssessmentAttempt\GetStudentAssessmentAttemptInfo;
use App\Http\Requests\StudentAssessmentAttempt\StartAttempt;
use App\Http\Services\StudentAssessmentAttemptService;
use Illuminate\Http\Request;

class StudentAssessmentAttemptController extends Controller
{
    public function __construct(private StudentAssessmentAttemptService  $studentAssessmentAttemptService) {}

    public function submitAttempt() {}

    public function updateAttemptAnswers() {}

    public function getStudentAssessmentAttemptInfo(GetStudentAssessmentAttemptInfo $request)
    {
        $validated = $request->validated();
        return $this->studentAssessmentAttemptService->getStudentAssessmentAttemptInfo($validated['student_id'], $validated['assessment_id']);
    }

    public function startAttempt(StartAttempt $request)
    {
        $validated = $request->validated();
        return $this->studentAssessmentAttemptService->startAttempt($validated['student_id'], $validated['assessment_id']);
    }
}
