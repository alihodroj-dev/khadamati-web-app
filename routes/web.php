<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;
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

            Route::get('/{id}/edit', [ServiceController::class, 'edit'])
                ->name('admin.services.edit');

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

    });

});
