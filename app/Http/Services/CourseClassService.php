<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\CourseRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Resources\CourseClassResource;
use Illuminate\Support\Facades\Log;

class CourseClassService
{
    private $courseClassRepo;
    private $fileAttachmentRepo;
    private $courseRepo;

    public function __construct(
        CourseClassRepository $courseClassRepo,
        FileAttachmentRepository $fileAttachmentRepo,
        CourseRepository $courseRepo
    ) {
        $this->courseClassRepo = $courseClassRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
        $this->courseRepo = $courseRepo;
    }

    public function getAll(array $filters)
    {
        $paginate = $filters['paginate'] ?? null;
        return CourseClassResource::collection($this->courseClassRepo->getAll(
            filters: $filters,
            relationships: ['course', 'semester'],
            paginate: empty($paginate) ? true : $paginate
        ));
    }

    public function findById(string $id)
    {
        return new CourseClassResource($this->courseClassRepo->findById($id, relationships: ['course', 'semester']));
    }

    public function create(array $formData)
    {
        $course = $this->courseRepo->findById($formData['course_id']);

        if (isset($formData['image'])) {
            $newImage = $this->fileAttachmentRepo->uploadAndCreate($formData['image']);
            $newCourseClass = $this->courseClassRepo->create(
                [...$formData, 'image_url' => $newImage->url],
                relationships: ['course', 'semester']
            );
        } else {
            $newCourseClass = $this->courseClassRepo->create(
                [...$formData, 'image_url' => $course->image_url],
                relationships: ['course', 'semester']
            );
        }
        return new CourseClassResource($newCourseClass);
    }

    public function updateById(string $id, array $formData)
    {
        $courseClass = $this->courseClassRepo->findById($id);
        $payloadImage = $formData['image'] ?? null;

        if ($courseClass->image_url) {
            if ($payloadImage) {
                // Delete the old image in any case
                $this->fileAttachmentRepo->deleteByFilter(['url' => $courseClass->image_url]);

                if ($payloadImage === "__DELETED__") {
                    //set image_url to course's image_url as default
                    $formData['image_url'] = $courseClass->course->image_url;
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

        return new CourseClassResource($this->courseClassRepo->updateById(
            $id,
            $formData,
            relationships: ['course', 'semester']
        ));
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
