<?php

namespace App\Jobs;

use App\Http\Services\StudentAssessmentAttemptService;
use App\Models\StudentAssessmentAttempt;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoSubmitExpiredAttempt implements ShouldQueue
{
    use Queueable, Dispatchable, SerializesModels, InteractsWithQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $attemptId) {}

    /**
     * Execute the job.
     */
    public function handle(StudentAssessmentAttemptService $studentAssessmentAttemptService): void
    {
        $attempt = StudentAssessmentAttempt::with('assessmentVersion.assessment.submissionSettings')->find($this->attemptId);

        if (!$attempt || $attempt->status !== 'ongoing') {
            return;
        }

        $settings = $attempt->assessmentVersion->assessment->submissionSettings;
        $now = now();

        // Re-verify expiry to avoid a race condition where the student
        // submitted normally just before this job ran
        $shouldAutoSubmit = false;

        if ($settings?->time_limit_seconds !== null) {
            $timeLimitExpiry = Carbon::parse($attempt->started_at)->addSeconds($settings->time_limit_seconds);
            if ($now->gte($timeLimitExpiry)) {
                $shouldAutoSubmit = true;
            }
        }

        if (
            !$shouldAutoSubmit &&
            $settings->due_date !== null &&
            $settings->after_due_date_behavior === 'auto_submit' &&
            $now->gte(Carbon::parse($settings->due_date))
        ) {
            $shouldAutoSubmit = true;
        }

        if (!$shouldAutoSubmit) {
            return;
        }

        Log::info('auto-submitting attempt ' . "$attempt->id");

        $studentAssessmentAttemptService->submitAttempt([
            'attempt_id' => $attempt->id,
            'answers' => $attempt->answers
        ]);
    }
}
