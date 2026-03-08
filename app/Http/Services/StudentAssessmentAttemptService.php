<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentResultRepository;
use App\Http\Repositories\StudentAssessmentAttemptRepository;
use App\Http\Resources\AssessmentResource;
use App\Http\Resources\ChapterContentResource;
use App\Http\Resources\StudentAssessmentAttemptResource;
use App\Models\StudentAssessmentAttempt;

class StudentAssessmentAttemptService
{
    public function __construct(
        private StudentAssessmentAttemptRepository $studentAssessmentAttemptRepo,
        private AssessmentResultRepository $assessmentResultRepo
    ) {}

    public function getAll()
    {
        return StudentAssessmentAttemptResource::collection($this->studentAssessmentAttemptRepo->getAll());
    }

    public function findById(string $id)
    {
        $attempt = $this->studentAssessmentAttemptRepo->findById($id, ['assessmentVersion']);
        $attemptAssessment = $attempt->assessmentVersion->assessment;
        $attemptAssessmentChapterContent = $attemptAssessment->chapterContent;
        $attemptAssessmentChapterContentChapter = $attemptAssessment->chapterContent->chapter;
        return new StudentAssessmentAttemptResource($attempt)
            ->additional([
                'assessment' => [
                    'id' => $attemptAssessment->id,
                    'chapterContent' => [
                        'id' => $attemptAssessmentChapterContent->id,
                        'name' => $attemptAssessmentChapterContent->name,
                        'chapter' => [
                            'id' => $attemptAssessmentChapterContentChapter->id,
                            'name' => $attemptAssessmentChapterContentChapter->name
                        ]
                    ]
                ]
            ]);
    }

    public function create(array $formData)
    {
        return new StudentAssessmentAttemptResource($this->studentAssessmentAttemptRepo->create($formData));
    }

    public function updateById(string $id, array $formData)
    {
        return new StudentAssessmentAttemptResource($this->studentAssessmentAttemptRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->studentAssessmentAttemptRepo->deleteById($id);
    }

    public function submitAttempt(array $formData)
    {
        $attempt = $this->studentAssessmentAttemptRepo->findById($formData['attempt_id'], ['assessmentVersion.assessment.chapterContent']);
        $answerKey = $attempt->assessmentVersion->answer_key;
        $answers = $formData['answers'];

        $submissionSummary = [];

        $totalPointsEarned = 0;

        $hasEssayItem = collect($answers)->contains('material_type', 'essayItem');

        foreach ($answers as $answer) {
            $materialId = $answer['asmt_material_id'];
            $materialType = $answer['material_type'];
            $answerContent = $answer['content'];
            $materialPointWorth = $answerKey[$materialId]['point_worth'];

            switch ($materialType) {
                case 'option_based_item':
                    $correctAnswer = $answerKey[$materialId]['correct_answer'];
                    $isCorrect = $answerContent === $correctAnswer;
                    $pointsEarned = $isCorrect ? $materialPointWorth : 0;

                    if ($isCorrect) $totalPointsEarned += $materialPointWorth;

                    $submissionSummary[$materialId] = [
                        'answer_content' => $answerContent,
                        'correct_answer' => $correctAnswer,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ];
                    break;

                case 'identification_item':
                    $acceptedAnswers = $answerKey[$materialId]['accepted_answers'];
                    $isCorrect = in_array($answerContent, $acceptedAnswers);
                    $pointsEarned = $isCorrect ? $materialPointWorth : 0;

                    if ($isCorrect) $totalPointsEarned += 0;

                    $submissionSummary[$materialId] = [
                        'answer_content' => $answerContent,
                        'accepted_answers' => $acceptedAnswers,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ];
                    break;
                case 'essay_item':
                    $submissionSummary[$materialId] = [
                        'answer_content' => $answerContent,
                        'points_earned' => null //ungraded initially, this will be manually checked by instructor
                    ];
            }
        }

        $this->studentAssessmentAttemptRepo->updateById($attempt->id, [
            'submission_summary' => $submissionSummary,
            'submitted_at' => now(),
            'status' => 'submitted',
            //essay items cannot be auto-graded
            'total_score' => $hasEssayItem ? null : $totalPointsEarned
        ]);

        $attempts = StudentAssessmentAttempt::where('student_id', $attempt->student_id)
            ->whereHas('assessmentVersion', function ($q) use ($attempt) {
                $q->where('id', $attempt->assesmentVersion->assessment_id);
            })
            ->pluck('final_score');

        $assessment = $attempt->assessmentVersion->assessment;
        $assessmentResult = $this->assessmentResultRepo->findByFilter([
            'assessment_id' => $assessment->id,
            'student_id' => $attempt->student_id
        ]);

        if (!$hasEssayItem) {
            if ($assessmentResult) {
                if ($assessment->max_attempts > 1) {
                    $assessmentResult->update([
                        'final_score' => $assessment->multi_attempt_grading_type === 'avg_score' ? $attempts->avg() : $attempts->max()
                    ]);
                } else {
                    $assessmentResult->update([
                        'final_score' => $totalPointsEarned
                    ]);
                }
            } else {
                $this->assessmentResultRepo->create([
                    'student_id' => $attempt->student_id,
                    'assessment_id' => $assessment->id,
                    'final_score' => $totalPointsEarned
                ]);
            }
        } else {
            if (!$assessmentResult) {
                $this->assessmentResultRepo->create([
                    'student_id' => $attempt->student_id,
                    'assessment_id' => $assessment->id,
                    'final_score' => null
                ]);
            }
        }

        return [
            'message' => 'attempt submitted successfully.'
        ];
    }

    public function getStudentAssessmentAttemptAvailability(string $studentId, string $assessmentId)
    {
        return $this->studentAssessmentAttemptRepo->getStudentAssessmentAttemptAvailability($studentId, $assessmentId);
    }

    public function startAttempt(string $studentId, string $assessmentId)
    {
        return new StudentAssessmentAttemptResource($this->studentAssessmentAttemptRepo->startAttempt($studentId, $assessmentId));
    }

    public function updateAttemptAnswers(string $attemptId, array $answers)
    {
        $this->studentAssessmentAttemptRepo->updateById($attemptId, [
            'answers' => $answers
        ]);

        return ['message' => 'answers updated.'];
    }

    public function updateAttemptAnswer(string $attemptId, array $incomingAnswer)
    {
        $attempt = $this->studentAssessmentAttemptRepo->findById($attemptId);

        $foundAnswer = collect($attempt->answers)->first(function ($answer) use ($incomingAnswer) {
            return $answer['asmt_material_id'] === $incomingAnswer['asmt_material_id'];
        });

        //if incoming answer not in answers
        //just add it
        if (!$foundAnswer) {
            $this->studentAssessmentAttemptRepo->updateByRecord($attempt,  [
                'answers' => [
                    ...$attempt->answers,
                    $incomingAnswer
                ]
            ]);

            return ['message' => 'successfully added new answer.'];
        }

        $updatedAnswers = collect($attempt->answers)->map(function ($existingAnswer) use ($incomingAnswer) {
            if ($existingAnswer['asmt_material_id'] === $incomingAnswer['asmt_material_id']) {
                return [
                    ...$existingAnswer,
                    'content' => $incomingAnswer['content']
                ];
            }
            return $existingAnswer;
        });

        $attempt->update([
            'answers' => $updatedAnswers->toArray()
        ]);

        return ['message' => 'successfully updated answer.'];
    }
}
