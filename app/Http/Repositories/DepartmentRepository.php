<?php

namespace App\Http\Repositories;

use App\Models\Department;

class DepartmentRepository extends BaseRepository
{

    public function __construct(Department $department)
    {
        parent::__construct($department);
    }

    public function getAll(
        array $filters = [],
        array $relationships = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Department::query();
        $query->with($relationships);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->where(function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(name) LIKE ?', ["{$searchQueryFilter}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["{$searchQueryFilter}%"]);
            });
        }

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
