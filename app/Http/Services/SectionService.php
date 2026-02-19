<?php

namespace App\Http\Services;

use App\Http\Repositories\SectionRepository;
use App\Http\Resources\SectionResource;

class SectionService
{
    public function __construct(
        private SectionRepository $sectionRepo
    ) {}

    public function getAll(array $filters)
    {
        $paginateFilter = $filters['paginate'] ?? null;
        return SectionResource::collection($this->sectionRepo->getAll(
            filters: $filters,
            paginate: $paginateFilter !== null ? $paginateFilter : true
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
