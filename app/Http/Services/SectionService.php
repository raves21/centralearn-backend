<?php

namespace App\Http\Services;

use App\Http\Repositories\SectionRepository;
use App\Http\Resources\SectionResource;

class SectionService
{
    private $sectionRepo;

    public function __construct(SectionRepository $sectionRepo)
    {
        $this->sectionRepo = $sectionRepo;
    }

    public function getAll(array $filters)
    {
        return SectionResource::collection($this->sectionRepo->getAll(
            filters: $filters,
            paginate: $filters['paginate'] ?: true
        ));
    }

    public function findById(string $id)
    {
        return new SectionResource($this->sectionRepo->findById($id));
    }

    public function create(array $formData)
    {
        return new SectionResource($this->sectionRepo->create($formData));
    }

    public function updateById(string $id, array $formData)
    {
        return new SectionResource($this->sectionRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->sectionRepo->deleteById($id);
    }
}
