<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cms:cleanup', function () {
    $otps=DB::table('admin_login_otps')->where('expires_at','<',now()->subDay())->delete();
    $sessions=DB::table('admin_sessions')->where('expires_at','<',now())->delete();
    $this->info("Removed $otps expired OTP records and $sessions expired sessions.");
})->purpose('Remove expired authentication records');

Schedule::command('cms:cleanup')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('cms:backup')->dailyAt('02:15')->withoutOverlapping()->when(fn()=>env('DB_BACKUP_ENABLED',false));
