<?php

namespace App\Http\Services;

use App\Http\Repositories\ClassStudentEnrollmentRepository;
use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Repositories\StudentRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\ClassStudentEnrollmentResource;
use App\Http\Resources\CourseClassResource;
use App\Http\Resources\SemesterResource;
use App\Http\Resources\StudentResource;
use App\Models\Role;
use DeepCopy\Filter\Filter;

class StudentService
{

    public function __construct(
        private StudentRepository $studentRepo,
        private SemesterRepository $semesterRepo,
        private CourseClassRepository $courseClassRepo,
        private ClassStudentEnrollmentRepository $classStudentEnrollmentRepo,
        private UserRepository $userRepo
    ) {}

    public function getAll(array $filters)
    {
        return StudentResource::collection($this->studentRepo->getAll(
            filters: $filters,
            relationships: [
                'program:id,name,code,department_id',
                'program.department:id,name,code'
            ]
        ));
    }

    public function create(array $formData)
    {
        $newUser = $this->userRepo->create($formData);
        $newUser->assignRole(Role::STUDENT);
        return new StudentResource($this->studentRepo->create([...$formData, 'user_id' => $newUser->id]));
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
        $user = $this->studentRepo->findById($id)->user;
        if (empty($formData['password'])) unset($formData['password']);
        $this->userRepo->updateById($user->id, $formData);
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

    public function getEnrollableClasses(string $studentId, array $filters)
    {
        $student = $this->studentRepo->findById($studentId);
        return CourseClassResource::collection(
            $this->courseClassRepo->getStudentEnrollableClasses($student, $filters)
        );
    }

    public function enrollToClass(string $studentId, string $classId)
    {
        $student = $this->studentRepo->findById($studentId);

        $enrollmentExists = $this->classStudentEnrollmentRepo->checkStudentEnrollmentExistence($studentId, $classId);
        if ($enrollmentExists) abort(409, 'Student is already enrolled in this class.');

        $this->courseClassRepo->verifyStudentDepartment($student, $classId);

        return new ClassStudentEnrollmentResource($this->classStudentEnrollmentRepo->create(
            formData: [
                'student_id' => $studentId,
                'course_class_id' => $classId
            ],
            relationships: ['student', 'courseClass.course', 'courseClass.semester']
        ));
    }

    public function unenrollToClass(string $studentId, string $classId)
    {
        $enrollmentExists = $this->classStudentEnrollmentRepo->checkStudentEnrollmentExistence($studentId, $classId);
        if (!$enrollmentExists) abort(404, 'Student is not enrolled in this class.');

        return $this->classStudentEnrollmentRepo->deleteByFilter([
            'student_id' => $studentId,
            'course_class_id' => $classId
        ]);
    }
}
