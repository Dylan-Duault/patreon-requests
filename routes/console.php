<?php

use Illuminate\Support\Facades\Schedule;

// Refresh Patreon memberships daily at midnight
Schedule::command('patreon:refresh-memberships')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// Grant monthly credits after memberships are refreshed (backup for users who didn't log in)
Schedule::command('credits:grant-monthly')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->runInBackground();
