<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentAssessmentAttempt\GetStudentAssessmentAttemptAvailability;
use App\Http\Requests\StudentAssessmentAttempt\StartAttempt;
use App\Http\Requests\StudentAssessmentAttempt\SubmitAttempt;
use App\Http\Requests\StudentAssessmentAttempt\UpdateAttemptAnswer;
use App\Http\Requests\StudentAssessmentAttempt\UpdateAttemptAnswers;
use App\Http\Services\StudentAssessmentAttemptService;
use Illuminate\Http\Request;

class StudentAssessmentAttemptController extends Controller
{
    public function __construct(private StudentAssessmentAttemptService  $studentAssessmentAttemptService) {}

    public function show(string $studentAssessmentAttemptId)
    {
        return $this->studentAssessmentAttemptService->findById($studentAssessmentAttemptId);
    }

    public function submitAttempt(SubmitAttempt $request)
    {
        return $this->studentAssessmentAttemptService->submitAttempt($request->validated());
    }

    public function updateAttemptAnswers(UpdateAttemptAnswers $request)
    {
        $validated = $request->validated();
        return $this->studentAssessmentAttemptService->updateAttemptAnswers($validated['attempt_id'], $validated['answers']);
    }

    public function updateAttemptAnswer(UpdateAttemptAnswer $request)
    {
        $validated = $request->validated();
        return $this->studentAssessmentAttemptService->updateAttemptAnswer($validated['attempt_id'], $validated['answer']);
    }

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

    public function getAttemptRemainingTime(string $studentAssessmentAttemptId)
    {
        return $this->studentAssessmentAttemptService->getAttemptRemainingTime($studentAssessmentAttemptId);
    }
}
