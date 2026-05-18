<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\StaffOfficeController;
use App\Http\Controllers\Staff\StaffRequestController;
use App\Http\Controllers\Staff\StaffAppointmentController;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicTrackingController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/track/{trackingToken}', [PublicTrackingController::class, 'show'])
    ->name('tracking.show');

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);

});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
        

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.readAll');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->prefix('admin')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');
        
        Route::prefix('services')->group(function () {

            Route::get('/', [ServiceController::class, 'index'])
                ->name('admin.services.index');

            Route::get('/create', [ServiceController::class, 'create'])
                ->name('admin.services.create');

            Route::post('/', [ServiceController::class, 'store'])
                ->name('admin.services.store');

            Route::get('/{id}/edit', [ServiceController::class, 'edit'])
                ->name('admin.services.edit');

            Route::put('/{id}', [ServiceController::class, 'update'])
                ->name('admin.services.update');

            Route::delete('/{id}', [ServiceController::class, 'destroy'])
                ->name('admin.services.destroy');
            
            Route::get('/{id}', [ServiceController::class, 'show'])
                ->name('admin.services.show');

        });

        Route::prefix('offices')->group(function () {

            Route::get('/', [OfficeController::class, 'index'])
                ->name('admin.offices.index');

            Route::get('/create', [OfficeController::class, 'create'])
                ->name('admin.offices.create');

            Route::post('/', [OfficeController::class, 'store'])
                ->name('admin.offices.store');

            Route::get('/{id}/edit', [OfficeController::class, 'edit'])
                ->name('admin.offices.edit');

            Route::put('/{id}', [OfficeController::class, 'update'])
                ->name('admin.offices.update');

            Route::delete('/{id}', [OfficeController::class, 'destroy'])
                ->name('admin.offices.destroy');

            Route::get('/{id}', [OfficeController::class, 'show'])
                ->name('admin.offices.show');

        });

        Route::prefix('categories')->group(function () {

            Route::get('/', [CategoryController::class, 'index'])
                ->name('admin.categories.index');

            Route::get('/create', [CategoryController::class, 'create'])
                ->name('admin.categories.create');

            Route::post('/', [CategoryController::class, 'store'])
                ->name('admin.categories.store');

            Route::get('/{id}/edit', [CategoryController::class, 'edit'])
                ->name('admin.categories.edit');

            Route::put('/{id}', [CategoryController::class, 'update'])
                ->name('admin.categories.update');

            Route::delete('/{id}', [CategoryController::class, 'destroy'])
                ->name('admin.categories.destroy');

            Route::get('/{id}', [CategoryController::class, 'show'])
                ->name('admin.categories.show');

        });

        Route::prefix('users')->group(function () {

            Route::get('/', [UserController::class, 'index'])
                ->name('admin.users.index');

            Route::get('/create', [UserController::class, 'create'])
                ->name('admin.users.create');

            Route::post('/', [UserController::class, 'store'])
                ->name('admin.users.store');

            Route::get('/{id}/edit', [UserController::class, 'edit'])
                ->name('admin.users.edit');

            Route::put('/{id}', [UserController::class, 'update'])
                ->name('admin.users.update');

            Route::delete('/{id}', [UserController::class, 'destroy'])
                ->name('admin.users.destroy');

            Route::get('/{id}', [UserController::class, 'show'])
                ->name('admin.users.show');

        });

        Route::prefix('requests')->group(function () {

            Route::get('/', [ServiceRequestController::class, 'index'])
                ->name('admin.requests.index');

            Route::get('/{id}', [ServiceRequestController::class, 'show'])
                ->name('admin.requests.show');

            Route::post('/{id}/assign', [ServiceRequestController::class, 'assignStaff'])
                ->name('admin.requests.assignStaff');

            Route::post('/{id}/status', [ServiceRequestController::class, 'updateStatus'])
                ->name('admin.requests.updateStatus');

            Route::delete('/{id}', [ServiceRequestController::class, 'destroy'])
                ->name('admin.requests.destroy');

        });

        Route::prefix('appointments')->group(function () {

            Route::get('/', [AppointmentController::class, 'index'])
                ->name('admin.appointments.index');

            Route::get('/{id}', [AppointmentController::class, 'show'])
                ->name('admin.appointments.show');

            Route::get('/{id}/edit', [AppointmentController::class, 'edit'])
                ->name('admin.appointments.edit');

            Route::put('/{id}', [AppointmentController::class, 'update'])
                ->name('admin.appointments.update');

            Route::delete('/{id}', [AppointmentController::class, 'destroy'])
                ->name('admin.appointments.destroy');

        });

        Route::prefix('payments')->group(function () {

            Route::get('/', [PaymentController::class, 'index'])
                ->name('admin.payments.index');

            Route::get('/{id}', [PaymentController::class, 'show'])
                ->name('admin.payments.show');

            Route::post('/{requestId}/create', [PaymentController::class, 'store'])
                ->name('admin.payments.store');

            Route::put('/{id}', [PaymentController::class, 'update'])
                ->name('admin.payments.update');

        });

        Route::prefix('municipalities')->group(function () {

            Route::get('/', [App\Http\Controllers\Admin\MunicipalityController::class, 'index'])
                ->name('admin.municipalities.index');

            Route::get('/create', [App\Http\Controllers\Admin\MunicipalityController::class, 'create'])
                ->name('admin.municipalities.create');

            Route::post('/', [App\Http\Controllers\Admin\MunicipalityController::class, 'store'])
                ->name('admin.municipalities.store');

            Route::get('/{municipality}/edit', [App\Http\Controllers\Admin\MunicipalityController::class, 'edit'])
                ->name('admin.municipalities.edit');

            Route::put('/{municipality}', [App\Http\Controllers\Admin\MunicipalityController::class, 'update'])
                ->name('admin.municipalities.update');

            Route::delete('/{municipality}', [App\Http\Controllers\Admin\MunicipalityController::class, 'destroy'])
                ->name('admin.municipalities.destroy');

            Route::get('/{municipality}', [App\Http\Controllers\Admin\MunicipalityController::class, 'show'])
                ->name('admin.municipalities.show');
        });

        Route::prefix('reports')->group(function () {

            Route::get('/', [ReportController::class, 'index'])
                ->name('admin.reports.index');

        });
    });

    /*
    |--------------------------------------------------------------------------
    | Staff Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('staff')->prefix('staff')->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])
            ->name('staff.dashboard');

        Route::get('/office', [StaffOfficeController::class, 'edit'])
            ->name('staff.office.edit');

        Route::put('/office', [StaffOfficeController::class, 'update'])
            ->name('staff.office.update');

        // REQUESTS
        Route::get('/requests', [StaffRequestController::class, 'index'])
            ->name('staff.requests.index');

        Route::get('/requests/{id}', [StaffRequestController::class, 'show'])
            ->name('staff.requests.show');

        Route::post('/requests/{id}/status', [StaffRequestController::class, 'updateStatus'])
            ->name('staff.requests.updateStatus');

        Route::post('/requests/{id}/assign', [StaffRequestController::class, 'assignStaff'])
            ->name('staff.requests.assignStaff');
        
        Route::post('/requests/{id}/upload', [StaffRequestController::class, 'uploadDocument'])
            ->name('staff.requests.uploadDocument');

            Route::prefix('appointments')->group(function () {

                Route::get('/', [StaffAppointmentController::class, 'index'])
                    ->name('staff.appointments.index');

                Route::get('/today', [StaffAppointmentController::class, 'today'])
                    ->name('staff.appointments.today');

                Route::get('/{id}', [StaffAppointmentController::class, 'show'])
                    ->name('staff.appointments.show');

                Route::post('/{id}/update', [StaffAppointmentController::class, 'update'])
                    ->name('staff.appointments.update');

            });
    });


    /*
    |--------------------------------------------------------------------------
    | Citizen Web Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('guest')->prefix('citizen')->name('citizen.auth.')->group(function () {
        
        // Login
        Route::get('/login', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('/login', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'login']);
        
        // OTP Verification
        Route::get('/otp', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'showOtpForm'])
            ->name('otp.form');
        Route::post('/otp/verify', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'verifyOtp'])
            ->name('otp.verify');
        Route::post('/otp/resend', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'resendOtp'])
            ->name('otp.resend');
        
        // Registration
        Route::get('/register', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'showRegisterForm'])
            ->name('register');
        Route::get('/verify-id', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'showIdentityVerificationForm'])
            ->name('verify-id');
        Route::post('/verify-id', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'processIdentityVerification'])
            ->name('verify-id.process');
        Route::post('/register/complete', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'completeRegistration'])
            ->name('register.complete');
        
        // Social Login
        Route::get('/auth/google', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'redirectToGoogle'])
            ->name('google');
        Route::get('/auth/google/callback', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'handleGoogleCallback'])
            ->name('google.callback');
    });

    Route::middleware(['auth', 'citizen'])->prefix('citizen')->name('citizen.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Citizen\CitizenDashboardController::class, 'index'])
            ->name('dashboard');
        
        // Logout
        Route::post('/logout', [App\Http\Controllers\Citizen\CitizenAuthController::class, 'logout'])
            ->name('logout');
        
        // Services
        Route::get('/services', [App\Http\Controllers\Citizen\CitizenServiceController::class, 'index'])
            ->name('services.index');
        Route::get('/services/{id}', [App\Http\Controllers\Citizen\CitizenServiceController::class, 'show'])
            ->name('services.show');
        Route::get('/services/{id}/request', [App\Http\Controllers\Citizen\CitizenServiceController::class, 'createRequest'])
            ->name('services.request.create');
        Route::post('/services/{id}/request', [App\Http\Controllers\Citizen\CitizenServiceController::class, 'storeRequest'])
            ->name('services.request.store');
        
        // Requests
        Route::get('/requests', [App\Http\Controllers\Citizen\CitizenRequestController::class, 'index'])
            ->name('requests.index');
        Route::get('/requests/{id}', [App\Http\Controllers\Citizen\CitizenRequestController::class, 'show'])
            ->name('requests.show');
        Route::post('/requests/{id}/cancel', [App\Http\Controllers\Citizen\CitizenRequestController::class, 'cancel'])
            ->name('requests.cancel');
        Route::get('/requests/{requestId}/documents/{documentId}/download', [App\Http\Controllers\Citizen\CitizenRequestController::class, 'downloadDocument'])
            ->name('requests.download-document');
        
        // Payments
        Route::get('/payments', [App\Http\Controllers\Citizen\CitizenPaymentController::class, 'index'])
            ->name('payments.index');
        Route::get('/payments/{id}', [App\Http\Controllers\Citizen\CitizenPaymentController::class, 'show'])
            ->name('payments.show');
        Route::get('/payments/checkout/{requestId}', [App\Http\Controllers\Citizen\CitizenPaymentController::class, 'checkout'])
            ->name('payments.checkout');
        Route::post('/payments/{paymentId}/process', [App\Http\Controllers\Citizen\CitizenPaymentController::class, 'process'])
            ->name('payments.process');
        
        // Appointments
        Route::get('/appointments', [App\Http\Controllers\Citizen\CitizenAppointmentController::class, 'index'])
            ->name('appointments.index');
        Route::get('/appointments/create/{requestId}', [App\Http\Controllers\Citizen\CitizenAppointmentController::class, 'create'])
            ->name('appointments.create');
        Route::post('/appointments/{requestId}', [App\Http\Controllers\Citizen\CitizenAppointmentController::class, 'store'])
            ->name('appointments.store');
        Route::get('/appointments/{id}', [App\Http\Controllers\Citizen\CitizenAppointmentController::class, 'show'])
            ->name('appointments.show');
        Route::post('/appointments/{id}/cancel', [App\Http\Controllers\Citizen\CitizenAppointmentController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::get('/appointments/availability/{officeId}', [App\Http\Controllers\Citizen\CitizenAppointmentController::class, 'getAvailableSlots'])
            ->name('appointments.availability');
        
        // Feedback
        Route::get('/feedback/create/{requestId}', [App\Http\Controllers\Citizen\CitizenFeedbackController::class, 'create'])
            ->name('feedback.create');
        Route::post('/feedback/{requestId}', [App\Http\Controllers\Citizen\CitizenFeedbackController::class, 'store'])
            ->name('feedback.store');
        Route::get('/feedback/{id}', [App\Http\Controllers\Citizen\CitizenFeedbackController::class, 'show'])
            ->name('feedback.show');
        Route::get('/feedback/{id}/edit', [App\Http\Controllers\Citizen\CitizenFeedbackController::class, 'edit'])
            ->name('feedback.edit');
        Route::put('/feedback/{id}', [App\Http\Controllers\Citizen\CitizenFeedbackController::class, 'update'])
            ->name('feedback.update');
        
        // Profile
        Route::get('/profile', [App\Http\Controllers\Citizen\CitizenProfileController::class, 'show'])
            ->name('profile.show');
        Route::get('/profile/edit', [App\Http\Controllers\Citizen\CitizenProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Citizen\CitizenProfileController::class, 'update'])
            ->name('profile.update');
        Route::post('/profile/password', [App\Http\Controllers\Citizen\CitizenProfileController::class, 'updatePassword'])
            ->name('profile.password');
    });
});

