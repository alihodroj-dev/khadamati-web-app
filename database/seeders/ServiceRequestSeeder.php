<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesReferenceNumbers;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    use GeneratesReferenceNumbers;

    public function run(): void
    {
        $citizen = User::query()->where('email', 'citizen@khadamati.com')->first();
        $citizen2 = User::query()->where('email', 'citizen2@khadamati.com')->first();
        $completeCitizen = User::query()->where('email', 'citizen.complete@khadamati.com')->first();
        $staff = User::query()->where('email', 'staff@khadamati.com')->first();
        $staff2 = User::query()->where('email', 'staff2@khadamati.com')->first();
        $beirutOffice = Office::query()->where('name', 'Beirut Central Services Office')->first();
        $hamraOffice = Office::query()->where('name', 'Hamra Citizen Service Center')->first();

        if (! $citizen || ! $beirutOffice) {
            return;
        }

        $birthCertificate = Service::query()->where('name', 'Birth Certificate Request')->first();
        $buildingPermit = Service::query()->where('name', 'Building Permit Request')->first();
        $taxClearance = Service::query()->where('name', 'Tax Clearance Request')->first();
        $scholarship = Service::query()->where('name', 'Scholarship Assistance Request')->first();

        $requests = [
            [
                'user' => $citizen,
                'service' => $birthCertificate,
                'reference' => 'SEED001',
                'status' => 'under_review',
                'citizen_notes' => 'Need the certificate for university enrollment.',
                'staff_notes' => 'Documents verified.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(5),
                'reviewed_at' => now()->subDays(2),
            ],
            [
                'user' => $citizen,
                'service' => $taxClearance,
                'reference' => 'SEED002',
                'status' => 'requires_action',
                'citizen_notes' => 'Missing utility bill upload.',
                'staff_notes' => 'Asked citizen to re-upload a clearer copy.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(3),
                'reviewed_at' => now()->subDay(),
            ],
            [
                'user' => $citizen,
                'service' => $buildingPermit,
                'office' => $hamraOffice ?? $beirutOffice,
                'reference' => 'SEED003',
                'status' => 'pending',
                'citizen_notes' => 'Renovation permit for apartment.',
                'assigned_staff_id' => null,
                'submitted_at' => now()->subHours(6),
            ],
            [
                'user' => $completeCitizen ?? $citizen,
                'service' => $scholarship,
                'reference' => 'SEED004',
                'status' => 'completed',
                'citizen_notes' => 'Scholarship application for fall semester.',
                'staff_notes' => 'Approved and certificate issued.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(20),
                'reviewed_at' => now()->subDays(10),
                'completed_at' => now()->subDays(7),
            ],
            [
                'user' => $completeCitizen ?? $citizen,
                'service' => $birthCertificate,
                'reference' => 'SEED005',
                'status' => 'cancelled',
                'citizen_notes' => 'Submitted by mistake.',
                'submitted_at' => now()->subDays(8),
            ],
            [
                'user' => $citizen2 ?? $citizen,
                'service' => $birthCertificate,
                'reference' => 'SEED006',
                'status' => 'under_review',
                'citizen_notes' => 'Need a copy for employment verification.',
                'staff_notes' => 'Initial review in progress.',
                'assigned_staff_id' => $staff2?->id ?? $staff?->id,
                'submitted_at' => now()->subDays(4),
                'reviewed_at' => now()->subDays(1),
            ],
            [
                'user' => $citizen2 ?? $citizen,
                'service' => $taxClearance,
                'reference' => 'SEED007',
                'status' => 'requires_action',
                'citizen_notes' => 'Uploaded documents from mobile.',
                'staff_notes' => 'Waiting for clearer utility bill.',
                'assigned_staff_id' => $staff?->id,
                'submitted_at' => now()->subDays(2),
                'reviewed_at' => now()->subHours(12),
            ],
        ];

        foreach ($requests as $data) {
            if (! $data['service']) {
                continue;
            }

            $reference = $this->uniqueReferenceNumber($data['reference']);
            $attributes = [
                    'user_id' => $data['user']->id,
                    'service_id' => $data['service']->id,
                    'office_id' => ($data['office'] ?? $beirutOffice)->id,
                    'assigned_staff_id' => $data['assigned_staff_id'] ?? null,
                    'status' => $data['status'],
                    'citizen_notes' => $data['citizen_notes'] ?? null,
                    'staff_notes' => $data['staff_notes'] ?? null,
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                    'submitted_data' => $data['submitted_data'] ?? null,
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
}
