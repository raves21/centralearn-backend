<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Resources\AssessmentMaterialResource;

class AssessmentMaterialService
{
    private $assessmentMaterialRepo;

    public function __construct(AssessmentMaterialRepository $assessmentMaterialRepo)
    {
        $this->assessmentMaterialRepo = $assessmentMaterialRepo;
    }

    public function getAll()
    {
        return AssessmentMaterialResource::collection($this->assessmentMaterialRepo->getAll());
    }

    public function findById(string $id)
    {
        return new AssessmentMaterialResource($this->assessmentMaterialRepo->findById($id));
    }

    public function create(array $formData)
    {
        return new AssessmentMaterialResource($this->assessmentMaterialRepo->create($formData));
    }

    public function updateById(string $id, array $formData)
    {
        return new AssessmentMaterialResource($this->assessmentMaterialRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->assessmentMaterialRepo->deleteById($id);
    }
}