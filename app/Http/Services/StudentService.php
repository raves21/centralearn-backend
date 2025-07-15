<?php

namespace App\Http\Services;

use App\Http\Repositories\StudentRepository;
use App\Http\Resources\StudentResource;

class StudentService
{

    protected $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    public function getAll()
    {
        return StudentResource::collection($this->studentRepo->getAll(relationships: [
            'program:id,name,code,department_id',
            'program.department:id,name,code'
        ]));
    }

    public function findById(string $id)
    {
        $student = $this->studentRepo->findById(id: $id, relationships: [
            'program:id,name,code,department_id',
            'program.department:id,name,code'
        ]);
        return new StudentResource($student);
    }

    public function currentUserStudentProfile()
    {
        return new StudentResource($this->studentRepo->currentUserStudentProfile());
    }
}
