<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\FeedbackResponse;
use App\Models\Payment;
use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AccountStatusNotification;
use App\Notifications\AppointmentUpdatedNotification;
use App\Notifications\DocumentUploadedNotification;
use App\Notifications\FeedbackResponseNotification;
use App\Notifications\PaymentUpdatedNotification;
use App\Notifications\RequestUpdatedNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $completeCitizen = User::query()->where('email', 'citizen.complete@khadamati.com')->first();

        if (! $citizen) {
            return;
        }

        if ($citizen->notifications()->exists()) {
            return;
        }

        $underReview = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED001')
            ->first();

        $completed = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED004')
            ->first();

        $document = RequestDocument::query()
            ->whereHas('serviceRequest', fn ($q) => $q->where('reference_number', 'like', '%SEED001'))
            ->first();

        $appointment = Appointment::query()->first();
        $payment = Payment::query()->where('status', 'pending')->first();
        $paidPayment = Payment::query()->where('status', 'paid')->first();
        $feedbackResponse = FeedbackResponse::query()->first();

        if ($underReview) {
            $citizen->notify(new RequestUpdatedNotification(
                $underReview,
                'Request under review',
                'Your birth certificate request is now under review.'
            ));

            if ($document) {
                $citizen->notify(new DocumentUploadedNotification(
                    $underReview,
                    $document,
                    'Document received',
                    'We received your national ID copy.'
                ));
            }
        }

        if ($appointment) {
            $citizen->notify(new AppointmentUpdatedNotification(
                $appointment,
                'Appointment scheduled',
                'Your building permit appointment is scheduled.'
            ));
        }

        if ($payment) {
            $citizen->notify(new PaymentUpdatedNotification(
                $payment,
                'Payment pending',
                'Complete your tax clearance payment to continue processing.'
            ));
        }

        if ($completeCitizen && $paidPayment) {
            $completeCitizen->notify(new PaymentUpdatedNotification(
                $paidPayment,
                'Payment received',
                'Your scholarship request payment has been recorded.'
            ));
        }

        if ($completeCitizen && $completed) {
            $completeCitizen->notify(new RequestUpdatedNotification(
                $completed,
                'Request completed',
                'Your scholarship assistance request has been completed.'
            ));
        }

        if ($completeCitizen && $feedbackResponse) {
            $completeCitizen->notify(new FeedbackResponseNotification($feedbackResponse));
        }

        $citizen->notify(new AccountStatusNotification(true));
    }
}
