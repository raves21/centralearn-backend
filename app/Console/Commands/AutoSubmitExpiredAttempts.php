<?php

namespace App\Console\Commands;

use App\Jobs\AutoSubmitExpiredAttempt;
use App\Models\StudentAssessmentAttempt;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoSubmitExpiredAttempts extends Command
{
    protected $signature = 'assessments:auto-submit-expired';
    protected $description = 'Dispatch auto-submission jobs for expired in-progress attempts.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        Log::info('RUNNING assessments:auto-submit-expired ' . "{$now}");

        StudentAssessmentAttempt::query()
            ->where('status', 'ongoing')
            ->with('assessmentVersion.assessment.submissionSettings')
            ->get()
            ->filter(function ($attempt) use ($now) {
                $settings = $attempt->assessmentVersion->assessment->submissionSettings;

                //Case 1: Time limit expired
                if ($settings->time_limit_seconds !== null) {
                    $timeLimitExpiry = Carbon::parse($attempt->started_at)->addSeconds($settings->time_limit_seconds);
                    if ($now->gte($timeLimitExpiry)) {
                        return true;
                    }
                }

                //Case 2: Due date passed and after due date behavior is auto submit
                if (
                    $settings->due_date !== null &&
                    $settings->after_due_date_behavior === 'auto_submit' &&
                    $now->gte(Carbon::parse($settings->due_date))
                ) {
                    return true;
                }

                return false;
            })
            ->each(function ($attempt) {
                AutoSubmitExpiredAttempt::dispatch($attempt->id);
            });
    }
}
