<?php

namespace App\Http\Repositories;

use App\Models\Assessment;
use App\Models\AssessmentVersion;
use App\Models\Student;
use App\Models\StudentAssessmentAttempt;

class StudentAssessmentAttemptRepository extends BaseRepository
{
    public function __construct(StudentAssessmentAttempt $studentAssessmentAttempt)
    {
        parent::__construct($studentAssessmentAttempt);
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

        $newAttempt = StudentAssessmentAttempt::create([
            'student_id' => $studentId,
            'assessment_version_id' => $latestAssessmentVersion->id,
            'attempt_number' => $studentAttemptCount + 1,
            'status' => 'ongoing',
            'answers' => [],
            'started_at' => now()
        ]);

        $newAttempt->load(['assessmentVersion']);

        return $newAttempt;
    }
}
