<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkronisasi data member dari API IBS setiap 30 menit
// Schedule::command('ibs:sync-members')
//     ->everyThirtyMinutes()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->onFailure(function () {
//         \Illuminate\Support\Facades\Log::error('[Scheduler] ibs:sync-members GAGAL dijalankan.');
//     });
Schedule::command('members:sync')->everyMinute();