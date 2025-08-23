<?php

namespace App\Http\Repositories;

use App\Models\Semester;
use Carbon\Carbon;

class SemesterRepository extends BaseRepository
{

    public function __construct(Semester $semester)
    {
        parent::__construct($semester);
    }

    public function getStudentEnrolledSemesters(string $studentId)
    {
        return Semester::whereHas('courseSemesters.studentEnrollments', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
            ->orderByDesc('start_date')
            ->get();
    }

    public function getInstructorAssignedSemesters(string $instructorId)
    {
        return Semester::whereHas('courseSemesters.instructorAssignments', function ($q) use ($instructorId) {
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
                'endDateMax' => self::formatDate($nextSemStartDate->addDay(1))
            ];
        } else if ($prevSemEndDate && !$nextSemStartDate) {
            return [
                'startDateMin' => self::formatDate($prevSemEndDate->addDay(1)),
                'endDateMax' => null
            ];
        } else if (!$prevSemEndDate && $nextSemStartDate) {
            return [
                'startDateMin' => null,
                'endDateMax' => self::formatDate($nextSemStartDate->addDay(1))
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
