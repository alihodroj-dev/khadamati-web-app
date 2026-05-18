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

// ========== CITIZEN AUTH CONTROLLER ==========
use App\Http\Controllers\Citizen\CitizenAuthController;
use App\Http\Controllers\Citizen\CitizenDashboardController;
use App\Http\Controllers\Citizen\CitizenServiceController;
use App\Http\Controllers\Citizen\CitizenRequestController;
use App\Http\Controllers\Citizen\CitizenPaymentController;
use App\Http\Controllers\Citizen\CitizenAppointmentController;
use App\Http\Controllers\Citizen\CitizenFeedbackController;
use App\Http\Controllers\Citizen\CitizenProfileController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Required)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/track/{trackingToken}', [PublicTrackingController::class, 'show'])
    ->name('tracking.show');

// Citizen OTP Routes - MUST be outside any auth middleware
Route::prefix('citizen')->name('citizen.auth.')->group(function () {
    Route::get('/otp', [CitizenAuthController::class, 'showOtpForm'])
        ->name('otp.form');
    Route::post('/otp/verify', [CitizenAuthController::class, 'verifyOtp'])
        ->name('otp.verify');
    Route::post('/otp/resend', [CitizenAuthController::class, 'resendOtp'])
        ->name('otp.resend');
});

/*
|--------------------------------------------------------------------------
| Guest Routes (Not Logged In)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Main Login
    Route::get('/login', [LoginController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Citizen Registration (no auth required)
    Route::prefix('citizen')->name('citizen.auth.')->group(function () {
        Route::get('/login', [CitizenAuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('/login', [CitizenAuthController::class, 'login']);
        Route::get('/register', [CitizenAuthController::class, 'showRegisterForm'])
            ->name('register');
        // ADD THIS LINE - Registration Step 1 - Store data
        Route::post('/register', [CitizenAuthController::class, 'storeRegistrationStep1'])
            ->name('register.store');
        Route::get('/verify-id', [CitizenAuthController::class, 'showIdentityVerificationForm'])
            ->name('verify-id');
        Route::post('/verify-id', [CitizenAuthController::class, 'processIdentityVerification'])
            ->name('verify-id.process');
        Route::post('/register/complete', [CitizenAuthController::class, 'completeRegistration'])
            ->name('register.complete');
        
        // Social Login
        Route::get('/auth/google', [CitizenAuthController::class, 'redirectToGoogle'])
            ->name('google');
        Route::get('/auth/google/callback', [CitizenAuthController::class, 'handleGoogleCallback'])
            ->name('google.callback');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Logged In)
|--------------------------------------------------------------------------
*/

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

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
        
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
        
        Route::resource('offices', OfficeController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('users', UserController::class);
        Route::resource('municipalities', App\Http\Controllers\Admin\MunicipalityController::class);
        
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [ServiceRequestController::class, 'index'])->name('index');
            Route::get('/{id}', [ServiceRequestController::class, 'show'])->name('show');
            Route::post('/{id}/assign', [ServiceRequestController::class, 'assignStaff'])->name('assignStaff');
            Route::post('/{id}/status', [ServiceRequestController::class, 'updateStatus'])->name('updateStatus');
            Route::delete('/{id}', [ServiceRequestController::class, 'destroy'])->name('destroy');
        });

        Route::resource('appointments', AppointmentController::class);
        Route::resource('payments', PaymentController::class);
        
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Staff Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('staff')->prefix('staff')->name('staff.')->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/office', [StaffOfficeController::class, 'edit'])->name('office.edit');
        Route::put('/office', [StaffOfficeController::class, 'update'])->name('office.update');

        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [StaffRequestController::class, 'index'])->name('index');
            Route::get('/{id}', [StaffRequestController::class, 'show'])->name('show');
            Route::post('/{id}/status', [StaffRequestController::class, 'updateStatus'])->name('status');
            Route::post('/{id}/assign', [StaffRequestController::class, 'assignStaff'])->name('assign');
            Route::post('/{id}/upload', [StaffRequestController::class, 'uploadDocument'])->name('upload');
        });

        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [StaffAppointmentController::class, 'index'])->name('index');
            Route::get('/today', [StaffAppointmentController::class, 'today'])->name('today');
            Route::get('/{id}', [StaffAppointmentController::class, 'show'])->name('show');
            Route::post('/{id}/update', [StaffAppointmentController::class, 'update'])->name('update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Citizen Routes (Logged In + Citizen Role)
    |--------------------------------------------------------------------------
    */

    Route::middleware('citizen')->prefix('citizen')->name('citizen.')->group(function () {
        
        Route::get('/dashboard', [CitizenDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [CitizenAuthController::class, 'logout'])->name('logout');
        
        // Services
        Route::get('/services', [CitizenServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{id}', [CitizenServiceController::class, 'show'])->name('services.show');
        Route::get('/services/{id}/request', [CitizenServiceController::class, 'createRequest'])->name('services.request.create');
        Route::post('/services/{id}/request', [CitizenServiceController::class, 'storeRequest'])->name('services.request.store');
        
        // Requests
        Route::get('/requests', [CitizenRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{id}', [CitizenRequestController::class, 'show'])->name('requests.show');
        Route::post('/requests/{id}/cancel', [CitizenRequestController::class, 'cancel'])->name('requests.cancel');
        Route::get('/requests/{requestId}/documents/{documentId}/download', [CitizenRequestController::class, 'downloadDocument'])->name('requests.download-document');
        
        // Payments
        Route::get('/payments', [CitizenPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{id}', [CitizenPaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/checkout/{requestId}', [CitizenPaymentController::class, 'checkout'])->name('payments.checkout');
        Route::post('/payments/{paymentId}/process', [CitizenPaymentController::class, 'process'])->name('payments.process');
        
        // Appointments
        Route::get('/appointments', [CitizenAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create/{requestId}', [CitizenAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments/{requestId}', [CitizenAppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/{id}', [CitizenAppointmentController::class, 'show'])->name('appointments.show');
        Route::post('/appointments/{id}/cancel', [CitizenAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::get('/appointments/availability/{officeId}', [CitizenAppointmentController::class, 'getAvailableSlots'])->name('appointments.availability');
        Route::get('/appointments/availability/staff', [CitizenAppointmentController::class, 'getAvailableStaff'])->name('appointments.availability.staff');
        
        // Feedback
        Route::get('/feedback/create/{requestId}', [CitizenFeedbackController::class, 'create'])->name('feedback.create');
        Route::post('/feedback/{requestId}', [CitizenFeedbackController::class, 'store'])->name('feedback.store');
        Route::get('/feedback/{id}', [CitizenFeedbackController::class, 'show'])->name('feedback.show');
        Route::get('/feedback/{id}/edit', [CitizenFeedbackController::class, 'edit'])->name('feedback.edit');
        Route::put('/feedback/{id}', [CitizenFeedbackController::class, 'update'])->name('feedback.update');
        
        // Profile
        Route::get('/profile', [CitizenProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [CitizenProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [CitizenProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [CitizenProfileController::class, 'updatePassword'])->name('profile.password');
    });
});