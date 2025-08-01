<?php

namespace App\Http\Services;

use App\Filament\Resources\SemesterResource;
use App\Http\Repositories\CourseSemesterRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Repositories\StudentRepository;
use App\Http\Resources\CourseSemesterResource;
use App\Http\Resources\StudentResource;

class StudentService
{

    private $studentRepo;
    private $semesterRepo;
    private $courseSemesterRepo;

    public function __construct(
        StudentRepository $studentRepo,
        SemesterRepository $semesterRepo,
        CourseSemesterRepository $courseSemesterRepo
    ) {
        $this->studentRepo = $studentRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseSemesterRepo = $courseSemesterRepo;
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

    public function getEnrolledSemesters(string $studentId)
    {
        $this->studentRepo->ensureExists($studentId);
        return SemesterResource::collection($this->semesterRepo->getStudentEnrolledSemesters($studentId));
    }

    public function getEnrolledCourses(string $studentId, array $filters)
    {
        $this->studentRepo->ensureExists($studentId);
        $studentEnrolledSemesters = $this->semesterRepo->getStudentEnrolledSemesters(studentId: $studentId);
        return CourseSemesterResource::collection(
            $this->courseSemesterRepo->getStudentEnrolledCourses(
                studentId: $studentId,
                filters: $filters,
                studentEnrolledSemesters: $studentEnrolledSemesters
            )
        );
    }
}
