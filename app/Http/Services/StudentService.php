<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Repositories\StudentRepository;
use App\Http\Resources\CourseResource;
use App\Http\Resources\StudentResource;
use App\Models\Student;

class StudentService
{

    protected $studentRepo;
    protected $semesterRepo;
    protected $courseRepo;

    public function __construct(
        StudentRepository $studentRepo,
        SemesterRepository $semesterRepo,
        CourseRepository $courseRepo
    ) {
        $this->studentRepo = $studentRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseRepo = $courseRepo;
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

    public function getEnrolledCourses(string $studentId, array $filters)
    {
        Student::findOrFail($studentId);
        $studentEnrolledSemesters = $this->semesterRepo->getStudentEnrolledSemesters(studentId: $studentId);
        return CourseResource::collection(
            $this->courseRepo->getStudentEnrolledCourses(
                studentId: $studentId,
                filters: $filters,
                studentEnrolledSemesters: $studentEnrolledSemesters
            )
        );
    }
}
