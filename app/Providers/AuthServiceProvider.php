<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\MunicipalityPolicy;
use App\Policies\OfficePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Appointment::class => AppointmentPolicy::class,
        Payment::class => PaymentPolicy::class,
        Feedback::class => FeedbackPolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
        User::class => UserPolicy::class,
        Service::class => ServicePolicy::class,
        ServiceCategory::class => ServiceCategoryPolicy::class,
        Municipality::class => MunicipalityPolicy::class,
        Office::class => OfficePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
