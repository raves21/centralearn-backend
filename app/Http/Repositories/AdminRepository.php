<?php

namespace App\Http\Repositories;

use App\Models\Admin;
use Illuminate\Support\Facades\Schema;

class AdminRepository extends BaseRepository
{
    public function __construct(Admin $admin)
    {
        parent::__construct($admin);
    }

    public function getAll(
        array $filters = [],
        array $relationships = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Admin::query();
        $query->with($relationships);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->whereHas('user', function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(first_name) LIKE ?', "{$searchQueryFilter}%")
                    ->orWhereRaw('LOWER(last_name) LIKE ?', "{$searchQueryFilter}%");
            });
        }

        foreach ($filters as $column => $value) {
            if (Schema::hasColumn((new Admin())->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
