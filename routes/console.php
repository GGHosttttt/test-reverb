<?php

use App\Console\Commands\PublishNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Events\MinuteTick;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::call(function () {
//     try {
//         event(new PublishNotification());
//         Log::info('Publish notification event dispatched at ' . now()->toDateTimeString());
//     } catch (\Exception $e) {
//         Log::error('Failed to dispatch MinuteTick event: ' . $e->getMessage());
//     }
// })->everyMinute()->name('minute-tick-task')
//     ->appendOutputTo(storage_path('logs/schedule.log'));


Schedule::command('publish-notification')
    ->everyMinute()
    ->name('notification-post-task')
    ->appendOutputTo(storage_path('logs/schedule.log'));
