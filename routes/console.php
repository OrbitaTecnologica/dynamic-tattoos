<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recordatorio de renovación: a diario avisa a quien renueva en ~1 mes.
Schedule::command('subscriptions:send-renewal-reminders')->dailyAt('08:00');
