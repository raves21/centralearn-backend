<?php

namespace App\Http\Repositories;

use App\Models\Program;
use App\Models\Semester;
use Illuminate\Support\Facades\Schema;

class ProgramRepository extends BaseRepository
{
    public function __construct(Program $program)
    {
        parent::__construct($program);
    }

    public function getAll(
        array $filters = [],
        array $relationships = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Program::query();
        $query->with($relationships);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->where(function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(name) LIKE ?', "%{$searchQueryFilter}%")
                    ->orWhereRaw('LOWER(code) LIKE ?', "%{$searchQueryFilter}%");
            });
        }

        foreach ($filters as $column => $value) {
            if ($column === 'name' || $column === 'code') continue;
            if (Schema::hasColumn((new Semester())->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
