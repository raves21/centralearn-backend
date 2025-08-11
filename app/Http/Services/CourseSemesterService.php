<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseSemesterRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Resources\CourseSemesterResource;

class CourseSemesterService
{
    private $courseSemesterRepo;
    private $fileAttachmentRepo;

    public function __construct(
        CourseSemesterRepository $courseSemesterRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->courseSemesterRepo = $courseSemesterRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return CourseSemesterResource::collection($this->courseSemesterRepo->getAll(filters: $filters, relationships: ['course', 'semester']));
    }

    public function findById(string $id)
    {
        return new CourseSemesterResource($this->courseSemesterRepo->findById($id, relationships: ['course', 'semester']));
    }

    public function create(array $formData)
    {
        if (isset($formData['image'])) {
            $newImage = $this->fileAttachmentRepo->uploadAndCreate($formData['image'], 'image');
            $newCourseSemester = $this->courseSemesterRepo->create(
                [...$formData, 'image_url' => $newImage->url],
                relationships: ['course', 'semester']
            );
        } else {
            $newCourseSemester = $this->courseSemesterRepo->create(
                $formData,
                relationships: ['course', 'semester']
            );
        }
        return new CourseSemesterResource($newCourseSemester);
    }

    public function updateById(string $id, array $formData)
    {
        $courseSemester = $this->courseSemesterRepo->findById($id);
        if (isset($formData['image'])) {
            if ($courseSemester->image_url) {
                //delete previous image
                $this->fileAttachmentRepo->deleteByFilter(['url' => $courseSemester->image_url]);
            }
            //upload new image
            $newImage = $this->fileAttachmentRepo->uploadAndCreate($formData['image'], 'image');
            $updatedCourseSemester = $this->courseSemesterRepo->updateById(
                $id,
                [...$formData, 'image_url' => $newImage->url],
                relationships: ['course', 'semester']
            );
        } else {
            $updatedCourseSemester = $this->courseSemesterRepo->updateById(
                $id,
                $formData,
                relationships: ['course', 'semester']
            );
        }
        return new CourseSemesterResource($updatedCourseSemester);
    }

    public function deleteById(string $id)
    {
        $courseSemester = $this->courseSemesterRepo->findById($id);
        if ($courseSemester->image_url) {
            $this->fileAttachmentRepo->deleteByFilter(['url' => $courseSemester->image_url]);
        }
        return $this->courseSemesterRepo->deleteById($id);
    }
}
