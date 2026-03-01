<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentAssessmentAttempt\GetStudentAssessmentAttemptAvailability;
use App\Http\Requests\StudentAssessmentAttempt\StartAttempt;
use App\Http\Services\StudentAssessmentAttemptService;
use Illuminate\Http\Request;

class StudentAssessmentAttemptController extends Controller
{
    public function __construct(private StudentAssessmentAttemptService  $studentAssessmentAttemptService) {}

    public function show(string $studentAssessmentAttemptId)
    {
        return $this->studentAssessmentAttemptService->findById($studentAssessmentAttemptId);
    }

    public function submitAttempt() {}

    public function updateAttemptAnswers() {}

    public function getStudentAssessmentAttemptAvailability(GetStudentAssessmentAttemptAvailability $request)
    {
        $validated = $request->validated();
        return $this->studentAssessmentAttemptService->getStudentAssessmentAttemptAvailability($validated['student_id'], $validated['assessment_id']);
    }

    public function startAttempt(StartAttempt $request)
    {
        $validated = $request->validated();
        return $this->studentAssessmentAttemptService->startAttempt($validated['student_id'], $validated['assessment_id']);
    }
}
