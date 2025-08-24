<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\InstructorRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\CourseClassResource;
use App\Http\Resources\InstructorResource;
use App\Http\Resources\SemesterResource;

class InstructorService
{
    private $instructorRepo;
    private $semesterRepo;
    private $courseClassRepo;

    public function __construct(
        InstructorRepository $instructorRepo,
        SemesterRepository $semesterRepo,
        CourseClassRepository $courseClassRepo
    ) {
        $this->instructorRepo = $instructorRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseClassRepo = $courseClassRepo;
    }

    public function getAll()
    {
        return InstructorResource::collection($this->instructorRepo->getAll(relationships: [
            'department:id,name,code'
        ]));
    }

    public function create(array $formData)
    {
        return new InstructorResource($this->instructorRepo->create($formData));
    }

    public function findById(string $id)
    {
        $instructor = $this->instructorRepo->findById(id: $id, relationships: [
            'department:id,name,code'
        ]);
        return new InstructorResource($instructor);
    }

    public function updateById(string $id, array $formData)
    {
        return new InstructorResource($this->instructorRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->instructorRepo->deleteById($id);
    }

    public function currentUserInstructorProfile()
    {
        return new InstructorResource($this->instructorRepo->currentUserInstructorProfile());
    }

    public function getAssignedSemesters(string $instructorId)
    {
        $this->instructorRepo->ensureExists($instructorId);
        return SemesterResource::collection($this->semesterRepo->getInstructorAssignedSemesters($instructorId));
    }

    public function getAssignedCourses(string $instructorId, array $filters)
    {
        $this->instructorRepo->ensureExists($instructorId);
        $instructorAssignedSemesters = $this->semesterRepo->getInstructorAssignedSemesters($instructorId);
        return CourseClassResource::collection(
            $this->courseClassRepo->getInstructorAssignedCourses(
                instructorId: $instructorId,
                instructorAssignedSemesters: $instructorAssignedSemesters,
                filters: $filters
            )
        );
    }
}
