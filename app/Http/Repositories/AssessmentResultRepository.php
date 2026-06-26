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
            'assessmentResult' => [
                'id' => $asmtResult->id,
                'maxScore' => $asmtResult->assessment->max_achievable_score,
                'finalScore' => $asmtResult->final_score,
                'lastRecordedAt' => $asmtResult->updated_at,
            ],
            'attempts' => $attempts->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'totalScore' => $attempt->total_score,
                    'status' => $attempt->status,
                    'attemptNumber' => $attempt->attempt_number,
                    'submittedAt' => $attempt->submitted_at,
                ];
            })->toArray()
        ];;
    }

    public function getAllByAssessment(string $assessmentId)
    {

        $results = AssessmentResult::with(['student.user', 'student.program', 'assessment.chapterContent'])->where('assessment_id', $assessmentId)->get();

        return $results->map(function ($asmtResult) use ($assessmentId) {
            $attempts = StudentAssessmentAttempt::whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })->get();

            $student = $asmtResult->student;

            return [
                'id' => $asmtResult->id,
                'assessmentId' => $assessmentId,
                'student' => [
                    'id' => $student->id,
                    'user' => [
                        'id' => $student->user->id,
                        'firstName' => $student->user->first_name,
                        'lastName' => $student->user->last_name
                    ],
                    'program' => [
                        'id' => $student->program->id,
                        'name' => $student->program->name,
                        'code' => $student->program->code,
                    ]
                ],
                'totalAttempts' => $attempts->count(),
                'finalScore' => $asmtResult->final_score,
            ];
        })->toArray();
    }

    public function getAttemptsByAssessmentResult(string $assessmentResultId)
    {
        $asmtResult = AssessmentResult::findOrFail($assessmentResultId)->load(['assessment', 'student']);


        $assessment = $asmtResult->assessment;
        $student = $asmtResult->student;

        $attempts = StudentAssessmentAttempt::with(['assessmentVersion', 'student'])->whereHas('assessmentVersion', function ($q) use ($assessment, $student) {
            $q->where('assessment_id', $assessment->id)
                ->where('student_id', $student->id);
        })->get();

        return $attempts->map(function ($attempt) use ($student) {
            return [
                'id' => $attempt->id,
                'student' => [
                    'id' => $student->id,
                    'user' => [
                        'id' => $student->user->id,
                        'firstName' => $student->user->first_name,
                        'lastName' => $student->user->last_name
                    ],
                    'program' => [
                        'id' => $student->program->id,
                        'name' => $student->program->name,
                        'code' => $student->program->code,
                    ]
                ],
                'questionnaireSnapshot' => $attempt->assessmentVersion->questionnaire_snapshot,
                'submissionSummary' => $attempt->submission_summary,
                'status' => $attempt->status,
                'attemptNumber' => $attempt->attempt_number,
                'startedAt' => (string) $attempt->started_at,
                'submittedAt' => (string) $attempt->submitted_at,
                'totalScore' => $attempt->total_score,
            ];
        })->toArray();
    }
}
