<?php

namespace App\Http\Services;

use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\SemesterResource;

class SemesterService
{

    private $semesterRepo;

    public function __construct(SemesterRepository $semesterRepo)
    {
        $this->semesterRepo = $semesterRepo;
    }

    public function getAll()
    {
        return SemesterResource::collection($this->semesterRepo->getAll());
    }

    public function findById(string $id)
    {
        return new SemesterResource($this->semesterRepo->findById($id));
    }
}
