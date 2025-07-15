<?php

namespace App\Http\Services;

use App\Http\Repositories\InstructorRepository;
use App\Http\Resources\InstructorResource;

class InstructorService
{
    protected $instructorRepo;

    public function __construct(InstructorRepository $instructorRepo)
    {
        $this->instructorRepo = $instructorRepo;
    }

    public function getAll()
    {
        return InstructorResource::collection($this->instructorRepo->getAll(relationships: [
            'department:id,name,code'
        ]));
    }

    public function findById(string $id)
    {
        $instructor = $this->instructorRepo->findById(id: $id, relationships: [
            'department:id,name,code'
        ]);
        return new InstructorResource($instructor);
    }

    public function currentUserInstructorProfile()
    {
        return new InstructorResource($this->instructorRepo->currentUserInstructorProfile());
    }
}
