<?php

namespace App\Http\Services;

use App\Http\Repositories\StudentAssessmentAttemptRepository;
use App\Http\Resources\StudentAssessmentAttemptResource;

class StudentAssessmentAttemptService
{
    private $studentAssessmentAttemptRepo;

    public function __construct(StudentAssessmentAttemptRepository $studentAssessmentAttemptRepo)
    {
        $this->studentAssessmentAttemptRepo = $studentAssessmentAttemptRepo;
    }

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
}