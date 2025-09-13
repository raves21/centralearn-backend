<?php

namespace App\Http\Repositories;

use App\Models\Instructor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class InstructorRepository extends BaseRepository
{
    public function __construct(Instructor $instructor)
    {
        parent::__construct($instructor);
    }

    public function currentUserInstructorProfile()
    {
        $instructor = Instructor::where('user_id', Auth::user()->id)->firstOrFail();
        return $instructor->load([
            'department:id,name,code'
        ]);
    }

    public function getAll(
        array $filters = [],
        array $relationships = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Instructor::query();
        $query->with($relationships)->orderBy($orderBy, $sortDirection);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->whereHas('user', function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(first_name) LIKE ?', "%{$searchQueryFilter}%")
                    ->orWhereRaw('LOWER(last_name) LIKE ?', "%{$searchQueryFilter}%");
            });
        }

        foreach ($filters as $column => $value) {
            if (Schema::hasColumn((new Instructor())->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
