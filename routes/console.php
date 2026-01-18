<?php

use Illuminate\Support\Facades\Schedule;

// Refresh Patreon memberships daily
Schedule::command('patreon:refresh-memberships')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();
