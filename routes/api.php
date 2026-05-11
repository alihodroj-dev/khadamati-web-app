<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\RequestDocumentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
Route::get('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'show']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/my-requests', [ServiceRequestController::class, 'myRequests']);
    Route::get('/my-requests/{serviceRequest}', [ServiceRequestController::class, 'show']);

    Route::get('/my-requests/{serviceRequest}/documents', [RequestDocumentController::class, 'index']);
    Route::post('/my-requests/{serviceRequest}/documents', [RequestDocumentController::class, 'store']);
    Route::delete('/my-requests/{serviceRequest}/documents/{document}', [RequestDocumentController::class, 'destroy']);
});
