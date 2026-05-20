<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DeviceToken;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Models\IdentityVerificationSession;
use App\Models\Office;
use App\Models\OtpChallenge;
use App\Models\Payment;
use App\Models\RequestDocument;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\SocialAccount;
use App\Models\User;
use App\Notifications\AccountStatusNotification;
use App\Notifications\AppointmentUpdatedNotification;
use App\Notifications\DocumentUploadedNotification;
use App\Notifications\FeedbackResponseNotification;
use App\Notifications\PaymentUpdatedNotification;
use App\Notifications\RequestUpdatedNotification;
use Database\Seeders\Concerns\GeneratesReferenceNumbers;
use Database\Seeders\Concerns\ResolvesAliHodrojCitizen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AliHodrojCitizenSeeder extends Seeder
{
    use GeneratesReferenceNumbers;
    use ResolvesAliHodrojCitizen;

    public function run(): void
    {
        $ali = $this->aliCitizen();

        if (! $ali) {
            return;
        }

        $staff = User::query()->where('email', 'staff@khadamati.com')->first();
        $staff2 = User::query()->where('email', 'staff2@khadamati.com')->first();
        $beirutOffice = Office::query()->where('name', 'Beirut Central Services Office')->first();
        $hamraOffice = Office::query()->where('name', 'Hamra Citizen Service Center')->first();

        if (! $beirutOffice) {
            return;
        }

        $birthCertificate = Service::query()->where('name', 'Birth Certificate Request')->first();
        $buildingPermit = Service::query()->where('name', 'Building Permit Request')->first();
        $taxClearance = Service::query()->where('name', 'Tax Clearance Request')->first();
        $scholarship = Service::query()->where('name', 'Scholarship Assistance Request')->first();

        $this->seedServiceRequests($ali, $staff, $staff2, $beirutOffice, $hamraOffice, $birthCertificate, $buildingPermit, $taxClearance, $scholarship);
        $this->seedRequestDocuments($ali, $staff);
        $this->seedAppointment($ali, $staff);
        $this->seedPayments($ali);
        $this->seedFeedback($ali, $staff);
        $this->seedDeviceToken($ali);
        $this->seedSocialAccount($ali);
        $this->seedOtpChallenge($ali);
        $this->seedIdentitySessions();
        $this->seedNotifications($ali);
    }

    protected function seedServiceRequests(
        User $ali,
        ?User $staff,
        ?User $staff2,
        Office $beirutOffice,
        ?Office $hamraOffice,
        ?Service $birthCertificate,
        ?Service $buildingPermit,
        ?Service $taxClearance,
        ?Service $scholarship,
    ): void {
        $requests = [
            [
                'service' => $birthCertificate,
                'reference' => 'ALI001',
                'status' => 'under_review',
                'citizen_notes' => 'Birth certificate needed for passport renewal.',
                'staff_notes' => 'All documents received; review in progress.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(6),
                'reviewed_at' => now()->subDays(2),
            ],
            [
                'service' => $taxClearance,
                'reference' => 'ALI002',
                'status' => 'requires_action',
                'citizen_notes' => 'Submitted tax clearance for property transfer.',
                'staff_notes' => 'Please re-upload the municipal tax receipt.',
                'assigned_staff_id' => $staff2?->id ?? $staff?->id,
                'submitted_at' => now()->subDays(3),
                'reviewed_at' => now()->subDay(),
            ],
            [
                'service' => $buildingPermit,
                'office' => $hamraOffice ?? $beirutOffice,
                'reference' => 'ALI003',
                'status' => 'pending',
                'citizen_notes' => 'Renovation permit for apartment balcony extension.',
                'assigned_staff_id' => null,
                'submitted_at' => now()->subHours(8),
            ],
            [
                'service' => $scholarship,
                'reference' => 'ALI004',
                'status' => 'completed',
                'citizen_notes' => 'Scholarship assistance for computer science program.',
                'staff_notes' => 'Approved; certificate issued.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(25),
                'reviewed_at' => now()->subDays(12),
                'completed_at' => now()->subDays(8),
            ],
            [
                'service' => $birthCertificate,
                'reference' => 'ALI005',
                'status' => 'cancelled',
                'citizen_notes' => 'Duplicate submission — cancelled by citizen.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(10),
            ],
        ];

        foreach ($requests as $data) {
            if (! $data['service']) {
                continue;
            }

            $reference = $this->uniqueReferenceNumber($data['reference']);
            $attributes = [
                'user_id' => $ali->id,
                'service_id' => $data['service']->id,
                'office_id' => ($data['office'] ?? $beirutOffice)->id,
                'assigned_staff_id' => $data['assigned_staff_id'] ?? null,
                'status' => $data['status'],
                'citizen_notes' => $data['citizen_notes'] ?? null,
                'staff_notes' => $data['staff_notes'] ?? null,
                'submitted_at' => $data['submitted_at'] ?? now(),
                'reviewed_at' => $data['reviewed_at'] ?? null,
                'completed_at' => $data['completed_at'] ?? null,
            ];

            if (! ServiceRequest::where('reference_number', $reference)->exists()) {
                $attributes['tracking_token'] = ServiceRequest::generateTrackingToken();
            }

            ServiceRequest::updateOrCreate(
                ['reference_number' => $reference],
                $attributes
            );
        }
    }

    protected function seedRequestDocuments(User $ali, ?User $staff): void
    {
        $underReview = $this->aliServiceRequest('ALI001');
        $completed = $this->aliServiceRequest('ALI004');

        if ($underReview) {
            RequestDocument::updateOrCreate(
                [
                    'service_request_id' => $underReview->id,
                    'document_type' => 'national_id_copy',
                ],
                [
                    'uploaded_by' => $ali->id,
                    'source' => RequestDocument::SOURCE_CITIZEN,
                    'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
                    'file_name' => 'ali-national-id.pdf',
                    'file_path' => 'request-documents/seed/'.$underReview->id.'/ali-national-id.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 248_000,
                    'status' => 'approved',
                ]
            );

            RequestDocument::updateOrCreate(
                [
                    'service_request_id' => $underReview->id,
                    'document_type' => 'family_registration',
                ],
                [
                    'uploaded_by' => $ali->id,
                    'source' => RequestDocument::SOURCE_CITIZEN,
                    'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
                    'file_name' => 'ali-family-record.jpg',
                    'file_path' => 'request-documents/seed/'.$underReview->id.'/ali-family-record.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 520_000,
                    'status' => 'pending',
                ]
            );
        }

        if ($completed && $staff) {
            RequestDocument::updateOrCreate(
                [
                    'service_request_id' => $completed->id,
                    'document_type' => 'certificate',
                ],
                [
                    'uploaded_by' => $staff->id,
                    'source' => RequestDocument::SOURCE_STAFF,
                    'purpose' => RequestDocument::PURPOSE_CERTIFICATE,
                    'file_name' => 'ali-scholarship-certificate.pdf',
                    'file_path' => 'request-documents/seed/'.$completed->id.'/ali-certificate.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 195_000,
                    'status' => 'approved',
                ]
            );
        }
    }

    protected function seedAppointment(User $ali, ?User $staff): void
    {
        $pending = $this->aliServiceRequest('ALI003');

        if (! $pending) {
            return;
        }

        Appointment::updateOrCreate(
            ['service_request_id' => $pending->id],
            [
                'user_id' => $ali->id,
                'staff_id' => $staff?->id,
                'appointment_date' => now()->addDays(4)->toDateString(),
                'appointment_time' => '11:30:00',
                'status' => 'scheduled',
                'notes' => 'Bring property deed and municipal approval draft.',
            ]
        );
    }

    protected function seedPayments(User $ali): void
    {
        $completed = $this->aliServiceRequest('ALI004');
        $underReview = $this->aliServiceRequest('ALI001');

        if ($completed) {
            Payment::updateOrCreate(
                ['transaction_reference' => 'TXN-ALI-PAID-001'],
                [
                    'service_request_id' => $completed->id,
                    'user_id' => $ali->id,
                    'amount' => 0.00,
                    'currency' => 'USD',
                    'payment_method' => 'card',
                    'status' => 'paid',
                    'payment_details' => [
                        'card_brand' => 'visa',
                        'last4' => '4242',
                    ],
                    'paid_at' => now()->subDays(9),
                ]
            );
        }

        if ($underReview) {
            Payment::updateOrCreate(
                ['transaction_reference' => 'TXN-ALI-PEND-001'],
                [
                    'service_request_id' => $underReview->id,
                    'user_id' => $ali->id,
                    'amount' => 5.00,
                    'currency' => 'USD',
                    'payment_method' => 'crypto',
                    'status' => 'pending',
                    'payment_details' => [
                        'wallet_address' => '0xali00000000000000000000000000000001',
                        'network' => 'ethereum-testnet',
                    ],
                ]
            );
        }
    }

    protected function seedFeedback(User $ali, ?User $staff): void
    {
        $completed = $this->aliServiceRequest('ALI004');

        if (! $completed) {
            return;
        }

        $feedback = Feedback::updateOrCreate(
            ['service_request_id' => $completed->id],
            [
                'user_id' => $ali->id,
                'rating' => 5,
                'comment' => 'Excellent service — scholarship processed quickly and staff were very helpful.',
            ]
        );

        if ($staff) {
            FeedbackResponse::updateOrCreate(
                [
                    'feedback_id' => $feedback->id,
                    'responder_id' => $staff->id,
                    'visibility' => FeedbackResponse::VISIBILITY_PUBLIC,
                ],
                [
                    'message' => 'Thank you Ali! We appreciate your feedback and are glad we could support your application.',
                ]
            );
        }
    }

    protected function seedDeviceToken(User $ali): void
    {
        DeviceToken::updateOrCreate(
            [
                'user_id' => $ali->id,
                'token' => 'seed-ios-fcm-token-ali-hodroj',
            ],
            [
                'platform' => DeviceToken::PLATFORM_IOS,
                'last_used_at' => now()->subHours(2),
            ]
        );
    }

    protected function seedSocialAccount(User $ali): void
    {
        SocialAccount::updateOrCreate(
            [
                'provider' => SocialAccount::PROVIDER_GOOGLE,
                'provider_user_id' => 'google-seed-ali-hodroj-2004',
            ],
            [
                'user_id' => $ali->id,
                'email' => $ali->email,
                'avatar_url' => 'https://example.com/avatars/ali-hodroj.jpg',
            ]
        );

        SocialAccount::updateOrCreate(
            [
                'provider' => SocialAccount::PROVIDER_APPLE,
                'provider_user_id' => 'apple-seed-ali-hodroj-2004',
            ],
            [
                'user_id' => $ali->id,
                'email' => $ali->email,
                'avatar_url' => null,
            ]
        );
    }

    protected function seedOtpChallenge(User $ali): void
    {
        OtpChallenge::updateOrCreate(
            ['challenge_token' => 'seed-ali-active-otp-challenge'],
            [
                'user_id' => $ali->id,
                'otp_hash' => Hash::make('112233'),
                'expires_at' => now()->addMinutes(15),
                'consumed_at' => null,
                'attempts' => 0,
                'channel' => OtpChallenge::CHANNEL_EMAIL,
            ]
        );

        OtpChallenge::updateOrCreate(
            ['challenge_token' => 'seed-ali-consumed-otp-challenge'],
            [
                'user_id' => $ali->id,
                'otp_hash' => Hash::make('998877'),
                'expires_at' => now()->subHour(),
                'consumed_at' => now()->subHours(3),
                'attempts' => 1,
                'channel' => OtpChallenge::CHANNEL_EMAIL,
            ]
        );
    }

    protected function seedIdentitySessions(): void
    {
        IdentityVerificationSession::updateOrCreate(
            ['session_token' => 'seed-ali-identity-verified'],
            [
                'id_front_path' => 'id-documents/seed-sessions/ali-verified-front.jpg',
                'id_back_path' => 'id-documents/seed-sessions/ali-verified-back.jpg',
                'ocr_raw_text' => 'Sample OCR output for Ali Hodroj Lebanese ID',
                'extracted_data' => [
                    'first_name' => 'Ali',
                    'last_name' => 'Hodroj',
                    'father_name' => 'Salah',
                    'mother_name' => 'Fatima Alyan',
                    'date_of_birth' => '2004-11-27',
                    'national_id' => '00073028821',
                ],
                'status' => IdentityVerificationSession::STATUS_VERIFIED,
                'expires_at' => now()->addHours(2),
            ]
        );

        IdentityVerificationSession::updateOrCreate(
            ['session_token' => 'seed-ali-identity-consumed'],
            [
                'id_front_path' => 'id-documents/seed-users/ali-hodroj-front.jpg',
                'id_back_path' => 'id-documents/seed-users/ali-hodroj-back.jpg',
                'ocr_raw_text' => 'Consumed identity session for Ali Hodroj',
                'extracted_data' => [
                    'first_name' => 'Ali',
                    'last_name' => 'Hodroj',
                    'national_id' => '00073028821',
                ],
                'status' => IdentityVerificationSession::STATUS_CONSUMED,
                'expires_at' => now()->subHour(),
            ]
        );
    }

    protected function seedNotifications(User $ali): void
    {
        if ($ali->notifications()->exists()) {
            return;
        }

        $underReview = $this->aliServiceRequest('ALI001');
        $requiresAction = $this->aliServiceRequest('ALI002');
        $completed = $this->aliServiceRequest('ALI004');
        $document = RequestDocument::query()
            ->whereHas('serviceRequest', fn ($q) => $q->where('reference_number', 'like', '%ALI001'))
            ->where('document_type', 'national_id_copy')
            ->first();
        $appointment = Appointment::query()
            ->where('user_id', $ali->id)
            ->first();
        $pendingPayment = Payment::query()->where('transaction_reference', 'TXN-ALI-PEND-001')->first();
        $paidPayment = Payment::query()->where('transaction_reference', 'TXN-ALI-PAID-001')->first();
        $feedbackResponse = FeedbackResponse::query()
            ->whereHas('feedback', fn ($q) => $q->where('user_id', $ali->id))
            ->first();

        if ($underReview) {
            $ali->notify(new RequestUpdatedNotification(
                $underReview,
                'Request under review',
                'Your birth certificate request is now under review.'
            ));

            if ($document) {
                $ali->notify(new DocumentUploadedNotification(
                    $underReview,
                    $document,
                    'Document received',
                    'We received your national ID copy.'
                ));
            }
        }

        if ($requiresAction) {
            $ali->notify(new RequestUpdatedNotification(
                $requiresAction,
                'Action required',
                'Please re-upload the municipal tax receipt for your tax clearance request.'
            ));
        }

        if ($appointment) {
            $ali->notify(new AppointmentUpdatedNotification(
                $appointment,
                'Appointment scheduled',
                'Your building permit consultation is scheduled.'
            ));
        }

        if ($pendingPayment) {
            $ali->notify(new PaymentUpdatedNotification(
                $pendingPayment,
                'Payment pending',
                'Complete your birth certificate payment to continue processing.'
            ));
        }

        if ($paidPayment) {
            $ali->notify(new PaymentUpdatedNotification(
                $paidPayment,
                'Payment received',
                'Your scholarship request payment has been recorded.'
            ));
        }

        if ($completed) {
            $ali->notify(new RequestUpdatedNotification(
                $completed,
                'Request completed',
                'Your scholarship assistance request has been completed.'
            ));
        }

        if ($feedbackResponse) {
            $ali->notify(new FeedbackResponseNotification($feedbackResponse));
        }

        $ali->notify(new AccountStatusNotification(true));
    }
}
