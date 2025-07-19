<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseRepository;
use App\Http\Repositories\InstructorRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\CourseResource;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;

class InstructorService
{
    protected $instructorRepo;
    protected $semesterRepo;
    protected $courseRepo;

    public function __construct(
        InstructorRepository $instructorRepo,
        SemesterRepository $semesterRepo,
        CourseRepository $courseRepo
    ) {
        $this->instructorRepo = $instructorRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseRepo = $courseRepo;
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

    public function getAssignedCourses(string $instructorId, array $filters)
    {
        Instructor::findOrFail($instructorId);
        $instructorAssignedSemesters = $this->semesterRepo->getInstructorAssignedSemesters($instructorId);
        return CourseResource::collection(
            $this->courseRepo->getInstructorAssignedCourses(
                instructorId: $instructorId,
                instructorAssignedSemesters: $instructorAssignedSemesters,
                filters: $filters
            )
        );
    }
}
