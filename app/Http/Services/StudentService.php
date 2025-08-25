<?php

namespace App\Http\Services;

use App\Http\Repositories\ClassStudentEnrollmentRepository;
use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Repositories\StudentRepository;
use App\Http\Resources\ClassStudentEnrollmentResource;
use App\Http\Resources\CourseClassResource;
use App\Http\Resources\SemesterResource;
use App\Http\Resources\StudentResource;

class StudentService
{

    private $studentRepo;
    private $semesterRepo;
    private $courseClassRepo;
    private $classStudentEnrollmentRepo;

    public function __construct(
        StudentRepository $studentRepo,
        SemesterRepository $semesterRepo,
        CourseClassRepository $courseClassRepo,
        ClassStudentEnrollmentRepository $classStudentEnrollmentRepo
    ) {
        $this->studentRepo = $studentRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseClassRepo = $courseClassRepo;
        $this->classStudentEnrollmentRepo = $classStudentEnrollmentRepo;
    }

    public function getAll()
    {
        return StudentResource::collection($this->studentRepo->getAll(relationships: [
            'program:id,name,code,department_id',
            'program.department:id,name,code'
        ]));
    }

    public function create(array $formData)
    {
        return new StudentResource($this->studentRepo->create($formData));
    }

    public function findById(string $id)
    {
        $student = $this->studentRepo->findById(id: $id, relationships: [
            'program:id,name,code,department_id',
            'program.department:id,name,code'
        ]);
        return new StudentResource($student);
    }

    public function updateById(string $id, array $formData)
    {
        return new StudentResource($this->studentRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->studentRepo->deleteById($id);
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

    public function getEnrolledClasses(string $studentId, array $filters)
    {
        $this->studentRepo->ensureExists($studentId);
        $studentEnrolledSemesters = $this->semesterRepo->getStudentEnrolledSemesters(studentId: $studentId);
        return CourseClassResource::collection(
            $this->courseClassRepo->getStudentEnrolledClasses(
                studentId: $studentId,
                filters: $filters,
                studentEnrolledSemesters: $studentEnrolledSemesters
            )
        );
    }

    public function getEnrollableClasses(string $studentId, string $semesterId)
    {
        $student = $this->studentRepo->findById($studentId);
        $semester = $this->semesterRepo->findById($semesterId);
        return CourseClassResource::collection(
            $this->courseClassRepo->getStudentEnrollableClasses($student, $semester)
        );
    }

    public function enrollToClass(string $studentId, string $classId)
    {
        $student = $this->studentRepo->findById($studentId);

        $this->studentRepo->ensureExists($studentId);
        $this->classStudentEnrollmentRepo->checkDuplicateClassStudentEnrollment($studentId, $classId);
        $this->courseClassRepo->verifyStudentDepartment($student, $classId);

        return new ClassStudentEnrollmentResource($this->classStudentEnrollmentRepo->create(
            formData: [
                'student_id' => $studentId,
                'course_class_id' => $classId
            ],
            relationships: ['student', 'courseClass.course', 'courseClass.semester']
        ));
    }
}
