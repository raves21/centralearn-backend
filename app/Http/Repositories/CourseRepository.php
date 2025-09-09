<?php

namespace App\Http\Repositories;

use App\Models\Course;
use Illuminate\Support\Facades\Schema;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $course)
    {
        parent::__construct($course);
    }

    public function syncToDepartments(Course $course, array $departmentIds)
    {
        $course->departments()->sync($departmentIds);
    }

    public function getAll(
        array $filters = [],
        array $relationships = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Course::query();
        $query->with($relationships);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->where(function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(name) LIKE ?', "{$searchQueryFilter}%")
                    ->orWhereRaw('LOWER(code) LIKE ?', "{$searchQueryFilter}%");
            });
        }

        foreach ($filters as $column => $value) {
            if (Schema::hasColumn((new Course())->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
