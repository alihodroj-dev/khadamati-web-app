<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;

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
                ->name('services.index');

            Route::get('/create', [ServiceController::class, 'create'])
                ->name('services.create');

            Route::post('/', [ServiceController::class, 'store'])
                ->name('services.store');

            Route::get('/{id}/edit', [ServiceController::class, 'edit'])
                ->name('services.edit');

            Route::put('/{id}', [ServiceController::class, 'update'])
                ->name('services.update');

            Route::delete('/{id}', [ServiceController::class, 'destroy'])
                ->name('services.destroy');
            
            Route::get('/{id}', [ServiceController::class, 'show'])
                ->name('services.show');

        });

        Route::prefix('categories')->group(function () {

            Route::get('/', [CategoryController::class, 'index'])
                ->name('categories.index');

            Route::get('/create', [CategoryController::class, 'create'])
                ->name('categories.create');

            Route::post('/', [CategoryController::class, 'store'])
                ->name('categories.store');

            Route::get('/{id}/edit', [CategoryController::class, 'edit'])
                ->name('categories.edit');

            Route::put('/{id}', [CategoryController::class, 'update'])
                ->name('categories.update');

            Route::delete('/{id}', [CategoryController::class, 'destroy'])
                ->name('categories.destroy');

            Route::get('/{id}', [CategoryController::class, 'show'])
                ->name('categories.show');

        });

        Route::prefix('users')->group(function () {

            Route::get('/', [UserController::class, 'index'])
                ->name('users.index');

            Route::get('/create', [UserController::class, 'create'])
                ->name('users.create');

            Route::post('/', [UserController::class, 'store'])
                ->name('users.store');

            Route::get('/{id}/edit', [UserController::class, 'edit'])
                ->name('users.edit');

            Route::put('/{id}', [UserController::class, 'update'])
                ->name('users.update');

            Route::delete('/{id}', [UserController::class, 'destroy'])
                ->name('users.destroy');

            Route::get('/{id}', [UserController::class, 'show'])
                ->name('users.show');

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
