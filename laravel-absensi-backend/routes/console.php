<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\MarkAlphaAttendance;
use App\Console\Commands\NotifyStudentsNotScanned;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(MarkAlphaAttendance::class)->dailyAt('08:00');
Schedule::command(NotifyStudentsNotScanned::class)->everyFifteenMinutes();