<?php

namespace App\Http\Repositories;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class SemesterRepository extends BaseRepository
{

    public function __construct(Semester $semester)
    {
        parent::__construct($semester);
    }

    public function getAll(
        array $relationships = [],
        array $filters = [],
        string $orderBy = 'created_at',
        string $sortDirection = 'desc',
        bool $paginate = true
    ) {
        $query = Semester::query();
        $query->with($relationships);

        $searchQueryFilter = strtolower($filters['query'] ?? '');

        if (!empty($searchQueryFilter)) {
            $query->where(function ($q) use ($searchQueryFilter) {
                $q->whereRaw('LOWER(name) LIKE ?', ["{$searchQueryFilter}%"]);
            });
        }

        foreach ($filters as $column => $value) {
            if ($column === 'name') continue;
            if (Schema::hasColumn((new Semester())->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if ($paginate) return $query->paginate();
        return $query->get();
    }

    public function getStudentEnrolledSemesters(string $studentId)
    {
        return Semester::whereHas('courseClasses.studentEnrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
            ->orderByDesc('start_date')
            ->get();
    }

    public function getInstructorAssignedSemesters(string $instructorId)
    {
        return Semester::whereHas('courseClasses.instructorAssignments', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
            ->orderByDesc('start_date')
            ->get();
    }

    public function updateSemesterGetMinMaxTimestamps(Semester $semester)
    {
        $prevSemEndDateQuery = Semester::orderByDesc('end_date')->where('end_date', '<', $semester->start_date)->first() ?? null;
        $prevSemEndDate = $prevSemEndDateQuery ? Carbon::parse($prevSemEndDateQuery->end_date) : null;

        $nextSemStartDateQuery = Semester::orderBy('end_date')->where('start_date', '>', $semester->end_date)->first() ?? null;
        $nextSemStartDate = $nextSemStartDateQuery ? Carbon::parse($nextSemStartDateQuery->start_date) : null;

        if ($prevSemEndDate && $nextSemStartDate) {
            return [
                'startDateMin' => self::formatDate($prevSemEndDate->addDay(1)),
                'endDateMax' => self::formatDate($nextSemStartDate->subDay(1))
            ];
        } else if ($prevSemEndDate && !$nextSemStartDate) {
            return [
                'startDateMin' => self::formatDate($prevSemEndDate->addDay(1)),
                'endDateMax' => null
            ];
        } else if (!$prevSemEndDate && $nextSemStartDate) {
            return [
                'startDateMin' => null,
                'endDateMax' => self::formatDate($nextSemStartDate->subDay(1))
            ];
        } else {
            return [
                'startDateMin' => null,
                'endDateMax' => null
            ];
        }
    }

    public function createSemesterGetMinMaxTimestamps()
    {
        return [
            'startDateMin' => self::formatDate(Carbon::parse(Semester::latest()->first()->end_date)->addDay(1)),
            'endDateMax' => null
        ];
    }

    private function formatDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
