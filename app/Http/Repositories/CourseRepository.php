<?php

namespace App\Http\Repositories;

use App\Models\Course;

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

        $nameFilter = strtolower($filters['name'] ?? '');

        if (!empty($nameFilter)) {
            $query->where(function ($q) use ($nameFilter) {
                $q->whereRaw('LOWER(name) LIKE ?', "{$nameFilter}%")
                    ->orWhereRaw('LOWER(code) LIKE ?', "{$nameFilter}%");
            });
        }

        foreach ($filters as $key => $value) {
            if ($key === 'name' || $key === 'code') continue;
            $query->where($key, $value);
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
