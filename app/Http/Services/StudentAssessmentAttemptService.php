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

        //update the answers
        $attempt->update([
            'answers' => $answers
        ]);

        $submissionSummary = [];

        $totalPointsEarned = 0;

        foreach ($answers as $answer) {
            $asmtMaterialId = $answer['asmt_material_id'];
            $materialType = $answer['material_type'];
            $answerContent = $answer['content'] ?? null;
            $materialPointWorth = $answerKey[$asmtMaterialId]['point_worth'];

            switch ($materialType) {
                case 'option_based_item':
                    $correctAnswer = $answerKey[$asmtMaterialId]['correct_answer'];
                    $isCorrect = $answerContent === $correctAnswer;
                    $pointsEarned = $isCorrect ? $materialPointWorth : 0;

                    if ($pointsEarned) $totalPointsEarned += $pointsEarned;

                    $submissionSummary[$asmtMaterialId] = [
                        'answer_content' => $answerContent,
                        'correct_answer' => $correctAnswer,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ];
                    break;

                case 'identification_item':
                    $acceptedAnswers = $answerKey[$asmtMaterialId]['accepted_answers'];
                    $isCorrect = in_array($answerContent, $acceptedAnswers);
                    $pointsEarned = $isCorrect ? $materialPointWorth : 0;

                    if ($pointsEarned) $totalPointsEarned += $pointsEarned;

                    $submissionSummary[$asmtMaterialId] = [
                        'answer_content' => $answerContent,
                        'accepted_answers' => $acceptedAnswers,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ];
                    break;
                case 'essay_item':
                    $submissionSummary[$asmtMaterialId] = [
                        'answer_content' => $answerContent,
                        //for essay items,
                        //if no answer (null), zero points earned. Otherwise set to null (to be graded and changed later by instructor)
                        'points_earned' => $answerContent ? null : 0
                    ];
            }
        }

        $hasAnswerWithNullPointsEarned = collect($submissionSummary)->first(fn($item) => $item['points_earned'] === null);

        $this->studentAssessmentAttemptRepo->updateById($attempt->id, [
            'submission_summary' => $submissionSummary,
            'submitted_at' => now(),
            'status' => 'submitted',
            'total_score' => $hasAnswerWithNullPointsEarned ? null : $totalPointsEarned
        ]);

        $attemptsTotalScores = StudentAssessmentAttempt::where('student_id', $attempt->student_id)
            ->whereHas('assessmentVersion', function ($q) use ($attempt) {
                $q->where('assessment_id', $attempt->assessmentVersion->assessment_id);
            })
            ->pluck('total_score');

        $assessment = $attempt->assessmentVersion->assessment;
        $assessmentResult = $this->assessmentResultRepo->findByFilter([
            'assessment_id' => $assessment->id,
            'student_id' => $attempt->student_id
        ]);

        //if has attempt with null total score, final score will be decided later (after instructor grades the essay item/s)
        if ($attemptsTotalScores->contains(null)) {
            if (!$assessmentResult) {
                $this->assessmentResultRepo->create([
                    'student_id' => $attempt->student_id,
                    'assessment_id' => $assessment->id,
                    'final_score' => null
                ]);
            } else {
                $assessmentResult->update([
                    'final_score' => null
                ]);
            }
        } else {
            if ($assessmentResult) {
                if ($assessment->max_attempts > 1) {
                    $assessmentResult->update([
                        'final_score' => $assessment->multi_attempt_grading_type === 'avg_score' ? $attemptsTotalScores->avg() : $attemptsTotalScores->max()
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
