<?php

namespace Database\Seeders;

use App\Models\RequestDocument;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class RequestDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();

        $underReview = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED001')
            ->first();

        $completed = ServiceRequest::query()
            ->where('reference_number', 'like', '%SEED004')
            ->first();

        if ($underReview && $citizen) {
            RequestDocument::updateOrCreate(
                [
                    'service_request_id' => $underReview->id,
                    'document_type' => 'national_id_copy',
                ],
                [
                    'uploaded_by' => $citizen->id,
                    'source' => RequestDocument::SOURCE_CITIZEN,
                    'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
                    'file_name' => 'national-id.pdf',
                    'file_path' => 'request-documents/seed/'.$underReview->id.'/national-id.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 245_000,
                    'status' => 'approved',
                ]
            );

            RequestDocument::updateOrCreate(
                [
                    'service_request_id' => $underReview->id,
                    'document_type' => 'family_registration',
                ],
                [
                    'uploaded_by' => $citizen->id,
                    'source' => RequestDocument::SOURCE_CITIZEN,
                    'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
                    'file_name' => 'family-record.jpg',
                    'file_path' => 'request-documents/seed/'.$underReview->id.'/family-record.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 512_000,
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
                    'file_name' => 'scholarship-certificate.pdf',
                    'file_path' => 'request-documents/seed/'.$completed->id.'/certificate.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 180_000,
                    'status' => 'approved',
                ]
            );
        }
    }
}
