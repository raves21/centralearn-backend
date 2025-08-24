<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Resources\CourseClassResource;

class CourseClassService
{
    private $courseClassRepo;
    private $fileAttachmentRepo;

    public function __construct(
        CourseClassRepository $courseClassRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->courseClassRepo = $courseClassRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return CourseClassResource::collection($this->courseClassRepo->getAll(filters: $filters, relationships: ['course', 'semester']));
    }

    public function findById(string $id)
    {
        return new CourseClassResource($this->courseClassRepo->findById($id, relationships: ['course', 'semester']));
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $newImage = $this->fileAttachmentRepo->uploadAndCreate($formData['image']);
            $newCourseClass = $this->courseClassRepo->create(
                [...$formData, 'image_url' => $newImage->url],
                relationships: ['course', 'semester']
            );
        } else {
            $newCourseClass = $this->courseClassRepo->create(
                $formData,
                relationships: ['course', 'semester']
            );
        }
        return new CourseClassResource($newCourseClass);
    }

    public function updateById(string $id, array $formData)
    {
        $courseClass = $this->courseClassRepo->findById($id);
        if (isset($formData['image'])) {
            if ($courseClass->image_url) {
                //delete previous image
                $this->fileAttachmentRepo->deleteByFilter(['url' => $courseClass->image_url]);
            }
            //upload new image
            $newImage = $this->fileAttachmentRepo->uploadAndCreate($formData['image']);
            $updatedCourseClass = $this->courseClassRepo->updateById(
                $id,
                [...$formData, 'image_url' => $newImage->url],
                relationships: ['course', 'semester']
            );
        } else {
            $updatedCourseClass = $this->courseClassRepo->updateById(
                $id,
                $formData,
                relationships: ['course', 'semester']
            );
        }
        return new CourseClassResource($updatedCourseClass);
    }

    public function deleteById(string $id)
    {
        $courseClass = $this->courseClassRepo->findById($id);
        if ($courseClass->image_url) {
            $this->fileAttachmentRepo->deleteByFilter(['url' => $courseClass->image_url]);
        }
        return $this->courseClassRepo->deleteById($id);
    }
}
