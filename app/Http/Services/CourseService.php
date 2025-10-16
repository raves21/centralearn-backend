<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Resources\CourseResource;

class CourseService
{
    private $courseRepo;
    private $fileAttachmentRepo;

    public function __construct(
        CourseRepository $courseRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->courseRepo = $courseRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return CourseResource::collection($this->courseRepo->getAll(
            relationships: ['departments'],
            filters: $filters
        ));
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $image = $this->fileAttachmentRepo->uploadAndCreate($formData['image']);
            $newCourse = $this->courseRepo->create([...$formData, 'image_url' => $image->url]);
        } else {
            $newCourse = $this->courseRepo->create($formData);
        }
        $this->courseRepo->syncToDepartments(course: $newCourse, departmentIds: $formData['departments']);
        return new CourseResource($this->courseRepo->loadRelationships(
            record: $newCourse,
            relationships: ['departments:id,code']
        ));
    }

    public function updateById(string $id, array $formData)
    {
        $course = $this->courseRepo->findById($id);
        $payloadImage = $formData['image'] ?? null;

        if ($course->image_url) {
            if ($payloadImage) {
                // Delete the old image in any case
                $this->fileAttachmentRepo->deleteByFilter(['url' => $course->image_url]);

                if ($payloadImage === "__DELETED__") {
                    $formData['image_url'] = null;
                } else {
                    // Upload new image and update image_url
                    $newImage = $this->fileAttachmentRepo->uploadAndCreate($payloadImage);
                    $formData['image_url'] = $newImage->url;
                }
            }
        } else {
            if ($payloadImage) {
                $newImage = $this->fileAttachmentRepo->uploadAndCreate($payloadImage);
                $formData['image_url'] = $newImage->url;
            }
        }

        $updatedCourse = $this->courseRepo->updateById(
            id: $id,
            formData: $formData,
        );


        if (isset($formData['departments'])) {
            $hasStudentEnrollment = $course->whereHas('courseClasses.studentEnrollments')->exists();
            if ($hasStudentEnrollment) {
                return response()->json([
                    'error' => 'cannot update this Course\'s Department/s because it has a student enrollment/s.'
                ], 409);
            } else {
                $this->courseRepo->syncToDepartments($updatedCourse, $formData['departments']);
            }
        }
        return new CourseResource($this->courseRepo->loadRelationships(
            record: $updatedCourse,
            relationships: ['departments:id,code']
        ));
    }

    public function findById(string $id)
    {
        return new CourseResource($this->courseRepo->findById(id: $id, relationships: ['departments:id,code']));
    }

    public function deleteById(string $id)
    {
        $course = $this->courseRepo->findById($id);
        if ($course->image_url) {
            $this->fileAttachmentRepo->deleteByFilter(['url' => $course->image_url]);
        }
        return $this->courseRepo->deleteById($id);
    }
}
