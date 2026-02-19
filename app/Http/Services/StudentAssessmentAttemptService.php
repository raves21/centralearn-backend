<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentRepository;
use App\Http\Repositories\AssessmentVersionRepository;
use App\Http\Repositories\StudentAssessmentAttemptRepository;
use App\Http\Resources\StudentAssessmentAttemptResource;

class StudentAssessmentAttemptService
{
    public function __construct(
        private StudentAssessmentAttemptRepository $studentAssessmentAttemptRepo,
        private AssessmentRepository $assessmentRepo,
        private AssessmentVersionRepository $assessmentVersionRepo
    ) {}

    public function getAll()
    {
        return StudentAssessmentAttemptResource::collection($this->studentAssessmentAttemptRepo->getAll());
    }

    public function findById(string $id)
    {
        return new StudentAssessmentAttemptResource($this->studentAssessmentAttemptRepo->findById($id));
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

        $attempt = $this->studentAssessmentAttemptRepo->findById($formData['attempt_id']);
        $answerKey = $attempt->assessmentVersion->answer_key;
        $answers = $formData['answers'];

        $submissionSummary = [];

        $totalPointsEarned = 0;

        $hasEssayItem = collect($answers)->contains('material_type', 'essayItem');

        foreach ($answers as $answer) {
            $materialId = $answer['material_id'];
            $materialType = $answer['material_type'];
            $answerContent = $answer['content'];

            switch ($materialType) {
                case 'optionBasedItem':
                    $correctAnswer = $answerKey[$materialId]['correct_answer'];
                    $isCorrect = $answerContent === $correctAnswer;
                    $pointsEarned = $isCorrect ? $answerKey[$materialId]['point_worth'] : 0;

                    if ($isCorrect) $totalPointsEarned += 0;

                    $submissionSummary[$materialId] = [
                        'answer_content' => $answerContent,
                        'correct_answer' => $correctAnswer,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ];
                    break;

                case 'identificationItem':
                    $acceptedAnswers = $answerKey[$materialId]['accepted_answers'];
                    $isCorrect = in_array($answerContent, $acceptedAnswers);
                    $pointsEarned = $isCorrect ? $answerKey[$materialId]['point_worth'] : 0;

                    if ($isCorrect) $totalPointsEarned += 0;

                    $submissionSummary[$materialId] = [
                        'answer_content' => $answerContent,
                        'accepted_answers' => $acceptedAnswers,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ];
                    break;
                case 'essayItem':
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

        return [
            'message' => 'attempt submitted successfully.'
        ];
    }
}
