<?php

namespace App\Http\Services;

use App\Http\Repositories\ClassInstructorAssignmentRepository;
use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\InstructorRepository;
use App\Http\Repositories\SemesterRepository;
use App\Http\Resources\ClassInstructorAssignmentResource;
use App\Http\Resources\CourseClassResource;
use App\Http\Resources\InstructorResource;
use App\Http\Resources\SemesterResource;

class InstructorService
{
    private $instructorRepo;
    private $semesterRepo;
    private $courseClassRepo;
    private $classInstructorAssignmentRepo;

    public function __construct(
        InstructorRepository $instructorRepo,
        SemesterRepository $semesterRepo,
        CourseClassRepository $courseClassRepo,
        ClassInstructorAssignmentRepository $classInstructorAssignmentRepo
    ) {
        $this->instructorRepo = $instructorRepo;
        $this->semesterRepo = $semesterRepo;
        $this->courseClassRepo = $courseClassRepo;
        $this->classInstructorAssignmentRepo = $classInstructorAssignmentRepo;
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

    public function getAssignedClasses(string $instructorId, array $filters)
    {
        $this->instructorRepo->ensureExists($instructorId);
        $instructorAssignedSemesters = $this->semesterRepo->getInstructorAssignedSemesters($instructorId);
        return CourseClassResource::collection(
            $this->courseClassRepo->getInstructorAssignedClasses(
                instructorId: $instructorId,
                instructorAssignedSemesters: $instructorAssignedSemesters,
                filters: $filters
            )
        );
    }

    public function getAssignableClasses(string $instructorId, string $semesterId)
    {
        $instructor = $this->instructorRepo->findById($instructorId);
        $semester = $this->semesterRepo->findById($semesterId);

        return CourseClassResource::collection($this->courseClassRepo->getInstructorAssignableClasses($instructor, $semester));
    }

    public function assignToClass(string $instructorId, string $classId)
    {
        $instructor = $this->instructorRepo->findById($instructorId);

        $classAssignmentExists = $this->classInstructorAssignmentRepo->checkClassAssignmentExistence($instructorId, $classId);
        if ($classAssignmentExists) abort(409, 'Instructor is already assigned in this class.');

        $this->courseClassRepo->verifyInstructorDepartment($instructor, $classId);

        return new ClassInstructorAssignmentResource(
            $this->classInstructorAssignmentRepo->create(
                formData: [
                    'instructor_id' => $instructorId,
                    'course_class_id' => $classId
                ],
                relationships: ['instructor', 'courseClass.course', 'courseClass.semester']
            )
        );
    }

    public function unassignToClass(string $instructorId, string $classId)
    {
        $classAssignmentExists = $this->classInstructorAssignmentRepo->checkClassAssignmentExistence($instructorId, $classId);
        if (!$classAssignmentExists) abort(404, 'Instructor is not assigned in this class.');

        return $this->classInstructorAssignmentRepo->deleteByFilter([
            'instructor_id' => $instructorId,
            'course_class_id' => $classId
        ]);
    }
}
