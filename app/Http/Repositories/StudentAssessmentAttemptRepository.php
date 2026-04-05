<?php

namespace App\Http\Repositories;

use App\Models\Assessment;
use App\Models\AssessmentVersion;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;
use App\Models\Student;
use App\Models\StudentAssessmentAttempt;
use Carbon\Carbon;

class StudentAssessmentAttemptRepository extends BaseRepository
{
    public function __construct(StudentAssessmentAttempt $studentAssessmentAttempt)
    {
        parent::__construct($studentAssessmentAttempt);
    }

    public function getAttemptsByStudentAndAssessment(string $studentId, string $assessmentId)
    {
        return StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->get();
    }

    public function countAssessmentOngoingAttempts(string $assessmentId)
    {
        return StudentAssessmentAttempt::where('status', 'ongoing')
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->distinct('student_id')
            ->count();
    }

    public function getStudentAssessmentAttemptAvailability(string $studentId, string $assessmentId)
    {
        $assessment = Assessment::findOrFail($assessmentId);
        $maxAttempts = $assessment->max_attempts;

        //count student attempts for this assessment
        $studentAttemptCount = StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->count();

        //get latest ongoing attempt
        $studentLatestOngoingAttempt = StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->where('status', 'ongoing')
            ->latest()
            ->first();

        if ($studentAttemptCount < $maxAttempts) {
            return [
                'attemptsLeft' => $maxAttempts - $studentAttemptCount,
                'continueAttempt' => $studentLatestOngoingAttempt
                    ? [
                        'attemptId' => $studentLatestOngoingAttempt->id,
                        'attemptNumber' => $studentLatestOngoingAttempt->attempt_number
                    ] : null,
                'canStartNewAttempt' => $studentLatestOngoingAttempt ? false : true
            ];
        }

        return [
            'attemptsLeft' => 0,
            'continueAttempt' => $studentLatestOngoingAttempt
                ? [
                    'attemptId' => $studentLatestOngoingAttempt->id,
                    'attemptNumber' => $studentLatestOngoingAttempt->attempt_number
                ] : null,
            'canStartNewAttempt' => false
        ];;
    }

    public function startAttempt(string $studentId, string $assessmentId)
    {
        $assessment = Assessment::findOrFail($assessmentId);
        $maxAttempts = $assessment->max_attempts;

        //count student attempts for this assessment
        $studentAttemptCount = StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->count();

        //restrict if student has exceeded max attempts
        if ($studentAttemptCount >= $maxAttempts) {
            abort(403, 'Student has reached max attempts for this assessment.');
        }

        //retrieve ongoing student attempts for this assesment
        $hasOngoingAttempts = StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->where('status', 'ongoing')
            ->exists();

        //restrict if student still has an ongoing attempt for this assessment
        if ($hasOngoingAttempts) {
            abort(403, 'Student still has has ongoing attempt for this assessment.');
        }

        //get latest assessment version
        $latestAssessmentVersion = AssessmentVersion::where('assessment_id', $assessmentId)->latest()->first();

        //initialize answers
        $initialAnswers = collect($latestAssessmentVersion->questionnaire_snapshot)->map(function ($item) {
            return [
                'asmt_material_id' => $item['id'],
                'material_type' => match ($item['materialType']) {
                    EssayItem::class => 'essay_item',
                    IdentificationItem::class => 'identification_item',
                    OptionBasedItem::class => 'option_based_item'
                },
                'content' => null
            ];
        })->toArray();

        $newAttempt = StudentAssessmentAttempt::create([
            'student_id' => $studentId,
            'assessment_version_id' => $latestAssessmentVersion->id,
            'attempt_number' => $studentAttemptCount + 1,
            'status' => 'ongoing',
            'answers' => $initialAnswers,
            'started_at' => now()
        ]);

        $newAttempt->load(['assessmentVersion']);

        return $newAttempt;
    }

    public function getAttemptRemainingTime(string $attemptId)
    {
        $attempt = StudentAssessmentAttempt::findOrFail($attemptId);

        $assessment = $attempt->assessmentVersion->assessment;

        $isAccessible = \App\Http\Services\ChapterContentService::isAccessible($assessment->chapterContent->id);

        if (!$isAccessible) {
            abort(400, 'This assessment is closed.');
        }

        $timeLimitSeconds = $assessment->submission_settings['time_limit_seconds'] ?? null;
        $dueDateStr = $assessment->submission_settings['due_date'] ?? null;
        $behavior = $assessment->submission_settings['after_due_date_behavior'] ?? null;

        $deadlineA = null;
        if ($timeLimitSeconds) {
            $deadlineA = $attempt->started_at->copy()->addSeconds($timeLimitSeconds);
        }

        $deadlineB = null;
        if ($dueDateStr && $behavior === 'auto_submit') {
            $deadlineB = Carbon::parse($dueDateStr);
        }

        $finalDeadline = null;
        if ($deadlineA && $deadlineB) {
            $finalDeadline = $deadlineA->min($deadlineB);
        } elseif ($deadlineA) {
            $finalDeadline = $deadlineA;
        } elseif ($deadlineB) {
            $finalDeadline = $deadlineB;
        }

        if (!$finalDeadline) {
            return null;
        }

        $remainingSeconds = (int) now()->diffInSeconds($finalDeadline, false);
        return $remainingSeconds > 0 ? $remainingSeconds : 0;
    }
}
