<?php

namespace App\Http\Services;

use App\Http\Repositories\ProgramRepository;
use App\Http\Resources\ProgramResource;

class ProgramService
{
    protected $programRepo;

    public function __construct(ProgramRepository $programRepo)
    {
        $this->programRepo = $programRepo;
    }

    public function getAll()
    {
        return ProgramResource::collection($this->programRepo->getAll(relationships: ['department']));
    }

    public function findById(string $id)
    {
        return new ProgramResource($this->programRepo->findById($id));
    }
}
