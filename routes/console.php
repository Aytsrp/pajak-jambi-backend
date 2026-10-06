<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tax:expire-transactions')->everyMinute();
Schedule::command('tax:send-reminders')->dailyAt('08:00');