<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminReportController;
use App\Http\Controllers\Api\MunicipalityController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\FeedbackResponseController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\StaffFeedbackController;
use App\Http\Controllers\Api\IdentityPreviewController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RequestDocumentController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\StaffOfficeController;
use App\Http\Controllers\Api\StaffRequestController;
use App\Http\Controllers\Api\StaffServiceController;
use App\Http\Controllers\Api\StaffTimeSlotBlockController;
use App\Http\Controllers\Api\StaffTimeSlotController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Citizen auth (iOS)
|--------------------------------------------------------------------------
*/
Route::post('/identity/preview', [IdentityPreviewController::class, 'preview']);
Route::post('/register/complete', [AuthController::class, 'completeRegistration']);
Route::post('/auth/google', [AuthController::class, 'google']);
Route::post('/auth/apple', [AuthController::class, 'apple']);
Route::post('/auth/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login/resend-otp', [AuthController::class, 'resendOtp']);

Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
Route::get('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'show']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}/feedback', [ServiceController::class, 'feedback']);
Route::get('/services/{service}', [ServiceController::class, 'show']);

Route::get('/track/{trackingToken}', [TrackingController::class, 'show']);

Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{office}/feedback', [OfficeController::class, 'feedback']);
Route::get('/offices/{office}', [OfficeController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Internal / deprecated (not for iOS)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/complete', [ProfileController::class, 'complete']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::patch('/profile/notification-preferences', [ProfileController::class, 'updateNotificationPreferences']);

    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens/{deviceToken}', [DeviceTokenController::class, 'destroy']);
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);
    Route::patch('/fcm-token', [FcmTokenController::class, 'update']);
    Route::delete('/fcm-token', [FcmTokenController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    Route::post('/service-requests', [ServiceRequestController::class, 'store']);

    Route::get('/my-requests', [ServiceRequestController::class, 'myRequests']);

    Route::get('/my-requests/{serviceRequest}', [
        ServiceRequestController::class,
        'show',
    ]);

    Route::get('/my-requests/{serviceRequest}/certificate', [
        ServiceRequestController::class,
        'certificate',
    ]);

    Route::patch('/my-requests/{serviceRequest}/cancel', [
        ServiceRequestController::class,
        'cancel',
    ]);

    Route::get('/my-requests/{serviceRequest}/documents', [
        RequestDocumentController::class,
        'index',
    ]);

    Route::post('/my-requests/{serviceRequest}/documents', [
        RequestDocumentController::class,
        'store',
    ]);

    Route::post('/my-requests/{serviceRequest}/documents/bulk', [
        RequestDocumentController::class,
        'bulkStore',
    ]);

    Route::get('/my-requests/{serviceRequest}/documents/{document}/download', [
        RequestDocumentController::class,
        'download',
    ]);

    Route::delete('/my-requests/{serviceRequest}/documents/{document}', [
        RequestDocumentController::class,
        'destroy',
    ]);

    Route::get('/appointments/availability', [AppointmentController::class, 'availability']);
    Route::apiResource('appointments', AppointmentController::class);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt']);
    Route::post('/payments/{payment}/process', [PaymentController::class, 'process']);
    Route::patch('/payments/{payment}/mark-paid', [PaymentController::class, 'markAsPaid']);
    Route::patch('/payments/{payment}/refund', [PaymentController::class, 'refund']);

    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show']);
    Route::patch('/feedback/{feedback}', [FeedbackController::class, 'update']);
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy']);

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/office', [StaffOfficeController::class, 'show']);
        Route::patch('/staff/office', [StaffOfficeController::class, 'update']);
        Route::get('/staff/feedback', [StaffFeedbackController::class, 'index']);

        Route::get('/staff/time-slots', [StaffTimeSlotController::class, 'index']);
        Route::post('/staff/time-slots', [StaffTimeSlotController::class, 'store']);
        Route::patch('/staff/time-slots/{officeTimeSlot}', [StaffTimeSlotController::class, 'update']);
        Route::delete('/staff/time-slots/{officeTimeSlot}', [StaffTimeSlotController::class, 'destroy']);

        Route::get('/staff/time-slot-blocks', [StaffTimeSlotBlockController::class, 'index']);
        Route::post('/staff/time-slot-blocks', [StaffTimeSlotBlockController::class, 'store']);
        Route::delete('/staff/time-slot-blocks/{officeTimeSlotBlock}', [StaffTimeSlotBlockController::class, 'destroy']);
    });

    // Staff & Admin: request management
    Route::middleware('role:staff,admin')->group(function () {
        Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
        Route::get('/staff/dashboard', [StaffRequestController::class, 'dashboard']);
        Route::get('/staff/requests', [StaffRequestController::class, 'index']);
        Route::get('/staff/requests/{serviceRequest}', [StaffRequestController::class, 'show']);
        Route::patch('/staff/requests/{serviceRequest}/status', [StaffRequestController::class, 'updateStatus']);
        Route::post('/staff/requests/{serviceRequest}/assign', [StaffRequestController::class, 'assign']);
        Route::get('/staff/appointments', [StaffRequestController::class, 'appointments']);
        Route::patch('/staff/appointments/{appointment}', [StaffRequestController::class, 'updateAppointment']);

        Route::get('/staff/service-categories', [StaffServiceController::class, 'categories']);
        Route::get('/staff/services', [StaffServiceController::class, 'index']);
        Route::post('/staff/services', [StaffServiceController::class, 'store']);
        Route::get('/staff/services/{service}', [StaffServiceController::class, 'show']);
        Route::patch('/staff/services/{service}', [StaffServiceController::class, 'update']);
        Route::delete('/staff/services/{service}', [StaffServiceController::class, 'destroy']);

        Route::post('/staff/feedback/{feedback}/responses', [FeedbackResponseController::class, 'store']);
        Route::patch('/staff/feedback-responses/{feedbackResponse}', [FeedbackResponseController::class, 'update']);
        Route::delete('/staff/feedback-responses/{feedbackResponse}', [FeedbackResponseController::class, 'destroy']);
    });

    // Admin only: assign requests to staff
    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/requests/{serviceRequest}/assign', [StaffRequestController::class, 'assign']);

        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser']);
        Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateRole']);
        Route::patch('/admin/users/{user}/activation', [AdminController::class, 'updateActivation']);
        Route::get('/admin/staff', [AdminController::class, 'staff']);

        Route::get('/admin/services', [AdminController::class, 'services']);
        Route::post('/admin/services', [AdminController::class, 'createService']);
        Route::patch('/admin/services/{service}', [AdminController::class, 'updateService']);
        Route::delete('/admin/services/{service}', [AdminController::class, 'deleteService']);

        Route::get('/admin/service-categories', [AdminController::class, 'categories']);
        Route::post('/admin/service-categories', [AdminController::class, 'createCategory']);
        Route::patch('/admin/service-categories/{serviceCategory}', [AdminController::class, 'updateCategory']);
        Route::delete('/admin/service-categories/{serviceCategory}', [AdminController::class, 'deleteCategory']);

        Route::get('/admin/municipalities', [MunicipalityController::class, 'index']);
        Route::post('/admin/municipalities', [MunicipalityController::class, 'store']);
        Route::patch('/admin/municipalities/{municipality}', [MunicipalityController::class, 'update']);
        Route::delete('/admin/municipalities/{municipality}', [MunicipalityController::class, 'destroy']);

        Route::get('/admin/reports/overview', [AdminReportController::class, 'overview']);

        Route::get('/admin/offices', [AdminController::class, 'offices']);
        Route::post('/admin/offices', [AdminController::class, 'createOffice']);
        Route::get('/admin/offices/{office}', [AdminController::class, 'showOffice']);
        Route::patch('/admin/offices/{office}', [AdminController::class, 'updateOffice']);
        Route::delete('/admin/offices/{office}', [AdminController::class, 'deleteOffice']);
    });

    // Messaging Routes: Citizen & Staff Communication

    // Conversations
    Route::get('/conversations/my', [ConversationController::class, 'myConversations']);
    Route::get('/conversations/my/{serviceRequest}', [ConversationController::class, 'getOrCreate']);
    Route::get('/conversations/staff', [ConversationController::class, 'staffConversations']);
    Route::get('/conversations/staff/{conversation}', [ConversationController::class, 'show']);
    Route::patch('/conversations/{conversation}/close', [ConversationController::class, 'close']);

    // Messages
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::get('/conversations/{conversation}/poll', [MessageController::class, 'poll']);
    Route::post('/conversations/{conversation}/typing', [MessageController::class, 'typing']);

    // Message utilities
    Route::patch('/conversations/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/conversations/messages/{message}', [MessageController::class, 'destroy']);

    // Unread counts
    Route::get('/conversations/unread/count', [MessageController::class, 'unreadCount']);
    Route::get('/conversations/unread/per-conversation', [MessageController::class, 'unreadPerConversation']);
    
});
