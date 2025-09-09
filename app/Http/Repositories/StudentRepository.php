<?php

namespace App\Http\Repositories;

use App\Http\Requests\ClassStudentEnrollment\GetStudentCourses;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class StudentRepository extends BaseRepository
{
    public function __construct(Student $student)
    {
        parent::__construct($student);
    }

    public function currentUserStudentProfile()
    {
        $student = Student::where('user_id', Auth::user()->id)->firstOrFail();
        return $student->load([
            'program:id,name,code,department_id',
            'program.department:id,name,code'
        ]);
    }

    public function getAll(
        array $filters = [],
        array $relationships = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Student::query();
        $query->with($relationships);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->whereHas('user', function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(first_name) LIKE ?', "%{$searchQueryFilter}%")
                    ->orWhereRaw('LOWER(last_name) LIKE ?', "%{$searchQueryFilter}%");
            });
        }

        foreach ($filters as $column => $value) {
            if (Schema::hasColumn((new Student())->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }
}
