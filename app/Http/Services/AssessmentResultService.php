<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentResultRepository;
use App\Http\Resources\AssessmentResultResource;

class AssessmentResultService
{
    private $assessmentResultRepo;

    public function __construct(AssessmentResultRepository $assessmentResultRepo)
    {
        $this->assessmentResultRepo = $assessmentResultRepo;
    }

    public function getAll()
    {
        return AssessmentResultResource::collection($this->assessmentResultRepo->getAll());
    }

    public function findById(string $id)
    {
        return new AssessmentResultResource($this->assessmentResultRepo->findById($id));
    }

    public function create(array $formData)
    {
        return new AssessmentResultResource($this->assessmentResultRepo->create($formData));
    }

    public function updateById(string $id, array $formData)
    {
        return new AssessmentResultResource($this->assessmentResultRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->assessmentResultRepo->deleteById($id);
    }
}