<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentVersionRepository;
use App\Http\Resources\AssessmentVersionResource;

class AssessmentVersionService
{
    private $assessmentVersionRepo;

    public function __construct(AssessmentVersionRepository $assessmentVersionRepo)
    {
        $this->assessmentVersionRepo = $assessmentVersionRepo;
    }

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

    public function updateById(string $id, array $formData)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->assessmentVersionRepo->deleteById($id);
    }
}