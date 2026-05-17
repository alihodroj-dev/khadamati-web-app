<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// DEFERRED(roadmap): Schedule appointment email/SMS reminders (e.g. SendAppointmentReminders).
// See docs/admin-office-roadmap.md#email--sms-appointment-reminders

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
