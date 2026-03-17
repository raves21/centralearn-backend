<?php

namespace App\Http\Repositories;

use App\Models\AssessmentResult;
use App\Models\StudentAssessmentAttempt;

class AssessmentResultRepository extends BaseRepository
{
    public function __construct(AssessmentResult $assessmentResult)
    {
        parent::__construct($assessmentResult);
    }

    public function getResultAndAttempts(string $studentId, string $assessmentId)
    {
        $asmtResult = AssessmentResult::where('student_id', $studentId)->where('assessment_id', $assessmentId)->first();

        if (!$asmtResult) {
            return abort(404, 'Assessment Result not found.');
        }

        $attempts = StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereHas('assessmentVersion', fn($q) => $q->where('assessment_id', $assessmentId))
            ->get();

        if ($attempts->isEmpty()) {
            return abort(404, 'No attempts found.');
        }

        return [
            'id' => $asmtResult->id,
            'maxScore' => $asmtResult->assessment->max_achievable_score,
            'finalScore' => $asmtResult->final_score,
            'lastRecordedAt' => $asmtResult->updated_at,
            'attempts' => $attempts->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'totalScore' => $attempt->total_score,
                    'status' => $attempt->status,
                    'attemptNumber' => $attempt->attempt_number,
                    'submittedAt' => $attempt->submitted_at,
                ];
            })->toArray()
        ];
    }
}
