<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('assessments:auto-submit-expired')->everyMinute()->withoutOverlapping();
