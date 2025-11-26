<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseClassRepository;
use App\Http\Repositories\CourseRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Resources\CourseClassResource;
use Illuminate\Support\Arr;
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
        return CourseClassResource::collection($this->courseClassRepo->getAll(
            filters: $filters,
            relationships: ['course', 'semester', 'section'],
            paginate: Arr::get($filters, 'paginate', true)
        ));
    }

    public function findById(string $id)
    {
        return new CourseClassResource($this->courseClassRepo->findById($id, relationships: ['course', 'semester', 'section']));
    }

    public function create(array $formData)
    {
        Log::info($formData);
        if (isset($formData['image'])) {
            $newImage = $this->fileAttachmentRepo->uploadAndCreate($formData['image']);
            $newCourseClass = $this->courseClassRepo->create(
                [...$formData, 'image_url' => $newImage->url],
                relationships: ['course', 'semester', 'section']
            );
        } else {
            $newCourseClass = $this->courseClassRepo->create(
                [...$formData, 'image_url' => $this->fileAttachmentRepo->getRandomDefaultImageUrl()],
                relationships: ['course', 'semester', 'section']
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
                $defaultImageUrls = $this->fileAttachmentRepo->getDefaultImagesUrls();
                // Delete the old image if its not one of the default image urls
                if (!in_array($courseClass->image_url, $defaultImageUrls)) {
                    $this->fileAttachmentRepo->deleteByFilter(['url' => $courseClass->image_url]);
                }

                if ($payloadImage === "__DELETED__") {
                    //as a default, set image_url to a random default image
                    $formData['image_url'] = $this->fileAttachmentRepo->getRandomDefaultImageUrl();
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
            relationships: ['course', 'semester', 'section']
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
