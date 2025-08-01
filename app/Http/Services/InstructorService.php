<?php

namespace App\Http\Services;

use App\Filament\Resources\SemesterResource;
use App\Http\Repositories\InstructorRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\CourseSemesterResource;
use App\Http\Resources\InstructorResource;
use App\Models\CourseSemester;
use App\Models\Instructor;

class InstructorService
{
    private $instructorRepo;
    private $semesterRepo;
    private $courseSemesterRepo;

    public function __construct(
        InstructorRepository $instructorRepo,
        SemesterRepository $semesterRepo,
        CourseSemester $courseSemesterRepo
    ) {
        $this->instructorRepo = $instructorRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseSemesterRepo = $courseSemesterRepo;
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

    public function getAssignedSemesters(string $instructorId)
    {
        $this->instructorRepo->ensureExists($instructorId);
        return SemesterResource::collection($this->semesterRepo->getInstructorAssignedSemesters($instructorId));
    }

    public function getAssignedCourses(string $instructorId, array $filters)
    {
        $this->instructorRepo->ensureExists($instructorId);
        $instructorAssignedSemesters = $this->semesterRepo->getInstructorAssignedSemesters($instructorId);
        return CourseSemesterResource::collection(
            $this->courseSemesterRepo->getInstructorAssignedCourses(
                instructorId: $instructorId,
                instructorAssignedSemesters: $instructorAssignedSemesters,
                filters: $filters
            )
        );
    }
}
