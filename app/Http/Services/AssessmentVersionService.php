<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentRepository;
use App\Http\Repositories\AssessmentVersionRepository;
use App\Http\Resources\AssessmentVersionResource;
use App\Models\Assessment;

class AssessmentVersionService
{
    public function __construct(
        private AssessmentVersionRepository $assessmentVersionRepo,
        private AssessmentRepository $assessmentRepo
    ) {}

    public function getAll()
    {
        return AssessmentVersionResource::collection($this->assessmentVersionRepo->getAll());
    }

    public function findById(string $id)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->findById($id));
    }

    public function create(array $formData)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->create($formData));
    }

    public function createFromAssessment(string $assessmentId)
    {
        $questionnaire = $this->buildQuestionnareSnapshot($this->assessmentRepo->findById($assessmentId));
    }

    public function buildQuestionnareSnapshot(Assessment $assessment) {}

    public function updateById(string $id, array $formData)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->assessmentVersionRepo->deleteById($id);
    }
}
