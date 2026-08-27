<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tax:send-reminders')->dailyAt('08:00');