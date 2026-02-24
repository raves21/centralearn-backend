<?php

namespace App\Http\Repositories;

use App\Models\Assessment;
use App\Models\StudentAssessmentAttempt;

class StudentAssessmentAttemptRepository extends BaseRepository
{
    public function __construct(StudentAssessmentAttempt $studentAssessmentAttempt)
    {
        parent::__construct($studentAssessmentAttempt);
    }

    public function countAssessmentOngoingAttempts(string $assessmentId)
    {
        return StudentAssessmentAttempt::where('status', 'ongoing')
            ->whereHas('assessmentVersion', function ($q) use ($assessmentId) {
                $q->where('assessment_id', $assessmentId);
            })
            ->distinct('student_id')
            ->count();
    }
}
