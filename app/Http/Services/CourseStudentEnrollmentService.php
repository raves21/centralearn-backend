<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseStudentEnrollmentRepository;
use App\Http\Resources\CourseStudentEnrollmentResource;

class CourseStudentEnrollmentService
{
    private $courseStudentEnrollmentRepo;

    public function __construct(CourseStudentEnrollmentRepository $courseStudentEnrollmentRepo)
    {
        $this->courseStudentEnrollmentRepo = $courseStudentEnrollmentRepo;
    }

    public function create(array $formData)
    {
        return new CourseStudentEnrollmentResource($this->courseStudentEnrollmentRepo->create(
            $formData,
            relationships: ['student', 'courseClass']
        ));
    }

    public function findById(string $id)
    {
        return new CourseStudentEnrollmentResource($this->courseStudentEnrollmentRepo->findById(
            $id,
            relationships: ['student', 'courseClass']
        ));
    }

    public function updateById(string $id, array $formData)
    {
        return new CourseStudentEnrollmentResource($this->courseStudentEnrollmentRepo->updateById(
            $id,
            $formData,
            relationships: ['student', 'courseClass']
        ));
    }

    public function deleteById(string $id)
    {
        return $this->courseStudentEnrollmentRepo->deleteById($id);
    }
}
