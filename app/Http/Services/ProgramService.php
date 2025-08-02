<?php

namespace App\Http\Services;

use App\Http\Repositories\ProgramRepository;
use App\Http\Resources\ProgramResource;

class ProgramService
{
    private $programRepo;

    public function __construct(ProgramRepository $programRepo)
    {
        $this->programRepo = $programRepo;
    }

    public function getAll(array $filters)
    {
        return ProgramResource::collection(
            $this->programRepo->getAll(relationships: ['department'], filters: $filters)
        );
    }

    public function create(array $formData)
    {
        return new ProgramResource($this->programRepo->create($formData, relationships: ['department']));
    }

    public function findById(string $id)
    {
        return new ProgramResource($this->programRepo->findById($id, relationships: ['department']));
    }

    public function updateById(string $id, array $formData)
    {
        return new ProgramResource($this->programRepo->updateById($id, $formData, relationships: ['department']));
    }

    public function deleteById(string $id)
    {
        return $this->programRepo->deleteById($id);
    }
}
