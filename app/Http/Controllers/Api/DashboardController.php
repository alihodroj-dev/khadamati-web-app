<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Support\StaffOfficeScope;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function statistics(Request $request)
    {
        $user = $request->user();
        $requestQuery = ServiceRequest::query();
        $appointmentQuery = Appointment::query();
        $paymentQuery = Payment::query();
        $feedbackQuery = Feedback::query();

        if ($user->isStaff()) {
            StaffOfficeScope::applyServiceRequestScope($requestQuery, $user);
            StaffOfficeScope::applyAppointmentScope($appointmentQuery, $user);
            StaffOfficeScope::applyPaymentScope($paymentQuery, $user);
            StaffOfficeScope::applyFeedbackScope($feedbackQuery, $user);
        }

        return $this->successResponse(
            [
                'requests' => [
                    'total' => (clone $requestQuery)->count(),
                    'pending' => (clone $requestQuery)->where('status', 'pending')->count(),
                    'under_review' => (clone $requestQuery)->where('status', 'under_review')->count(),
                    'requires_action' => (clone $requestQuery)->where('status', 'requires_action')->count(),
                    'approved' => (clone $requestQuery)->where('status', 'approved')->count(),
                    'rejected' => (clone $requestQuery)->where('status', 'rejected')->count(),
                    'completed' => (clone $requestQuery)->where('status', 'completed')->count(),
                    'cancelled' => (clone $requestQuery)->where('status', 'cancelled')->count(),
                ],
                'appointments' => [
                    'total' => (clone $appointmentQuery)->count(),
                    'scheduled' => (clone $appointmentQuery)->where('status', 'scheduled')->count(),
                    'confirmed' => (clone $appointmentQuery)->where('status', 'confirmed')->count(),
                    'cancelled' => (clone $appointmentQuery)->where('status', 'cancelled')->count(),
                    'completed' => (clone $appointmentQuery)->where('status', 'completed')->count(),
                    'missed' => (clone $appointmentQuery)->where('status', 'missed')->count(),
                ],
                'payments' => [
                    'total' => (clone $paymentQuery)->count(),
                    'pending' => (clone $paymentQuery)->where('status', 'pending')->count(),
                    'paid' => (clone $paymentQuery)->where('status', 'paid')->count(),
                    'failed' => (clone $paymentQuery)->where('status', 'failed')->count(),
                    'refunded' => (clone $paymentQuery)->where('status', 'refunded')->count(),
                    'paid_total' => (float) (clone $paymentQuery)->where('status', 'paid')->sum('amount'),
                    'refunded_total' => (float) (clone $paymentQuery)->where('status', 'refunded')->sum('amount'),
                ],
                'feedback' => [
                    'total' => (clone $feedbackQuery)->count(),
                    'average_rating' => round((float) (clone $feedbackQuery)->avg('rating'), 2),
                ],
            ],
            'Dashboard statistics retrieved successfully.'
        );
    }
}
