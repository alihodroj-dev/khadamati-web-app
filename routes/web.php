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

        // REQUESTS
        Route::get('/requests', [StaffRequestController::class, 'index'])
            ->name('staff.requests.index');

        Route::get('/requests/{id}', [StaffRequestController::class, 'show'])
            ->name('staff.requests.show');

        Route::post('/requests/{id}/status', [StaffRequestController::class, 'updateStatus'])
            ->name('staff.requests.updateStatus');
        
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
});
