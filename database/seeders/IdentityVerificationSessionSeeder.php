<?php

namespace Database\Seeders;

use App\Models\IdentityVerificationSession;
use Illuminate\Database\Seeder;

class IdentityVerificationSessionSeeder extends Seeder
{
    public function run(): void
    {
        IdentityVerificationSession::updateOrCreate(
            ['session_token' => 'seed-identity-session-pending'],
            [
                'id_front_path' => 'id-documents/seed-sessions/pending-front.jpg',
                'id_back_path' => 'id-documents/seed-sessions/pending-back.jpg',
                'ocr_raw_text' => null,
                'extracted_data' => null,
                'status' => IdentityVerificationSession::STATUS_PENDING,
                'expires_at' => now()->addHour(),
            ]
        );

        IdentityVerificationSession::updateOrCreate(
            ['session_token' => 'seed-identity-session-verified'],
            [
                'id_front_path' => 'id-documents/seed-sessions/verified-front.jpg',
                'id_back_path' => 'id-documents/seed-sessions/verified-back.jpg',
                'ocr_raw_text' => 'Sample OCR output for Lebanese ID',
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
            ['session_token' => 'seed-identity-session-consumed'],
            [
                'id_front_path' => 'id-documents/seed-sessions/consumed-front.jpg',
                'id_back_path' => 'id-documents/seed-sessions/consumed-back.jpg',
                'ocr_raw_text' => 'Consumed session OCR text',
                'extracted_data' => [
                    'first_name' => 'Sara',
                    'last_name' => 'Mansour',
                    'national_id' => 'CTZ-000002',
                ],
                'status' => IdentityVerificationSession::STATUS_CONSUMED,
                'expires_at' => now()->subHour(),
            ]
        );
    }
}
