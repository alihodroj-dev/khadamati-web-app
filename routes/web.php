<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\AppointmentController;


use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\StaffRequestController;

use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);

});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

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
    });

});
