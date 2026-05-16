<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Policies\AppointmentPolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ServiceRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Appointment::class => AppointmentPolicy::class,
        Payment::class => PaymentPolicy::class,
        Feedback::class => FeedbackPolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
