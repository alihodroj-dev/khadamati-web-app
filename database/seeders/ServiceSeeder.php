<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\Concerns\SeedImageUrls;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    use SeedImageUrls;
    public function run(): void
    {
        $beirutOffice = Office::query()->where('name', 'Beirut Central Services Office')->first();
        $hamraOffice = Office::query()->where('name', 'Hamra Citizen Service Center')->first();
        $tripoliOffice = Office::query()->where('name', 'Tripoli Main Office')->first();

        $services = [
            [
                'category' => 'Civil Records',
                'name' => 'Birth Certificate Request',
                'description' => 'Request an official birth certificate document.',
                'base_fee' => 5.00,
                'estimated_processing_days' => 3,
                'required_documents' => [
                    'National ID copy',
                    'Family registration document',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $beirutOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Civil Records',
                'name' => 'Marriage Certificate Request',
                'description' => 'Request an official marriage certificate document.',
                'base_fee' => 7.50,
                'estimated_processing_days' => 4,
                'required_documents' => [
                    'National ID copy',
                    'Family registration document',
                    'Marriage registration proof',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $beirutOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Civil Records',
                'name' => 'Family Record Statement',
                'description' => 'Request an official family record statement.',
                'base_fee' => 6.00,
                'estimated_processing_days' => 3,
                'required_documents' => [
                    'National ID copy',
                    'Family registration number',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $beirutOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Municipality Services',
                'name' => 'Building Permit Request',
                'description' => 'Submit a request for a building or renovation permit.',
                'base_fee' => 50.00,
                'estimated_processing_days' => 14,
                'required_documents' => [
                    'Property ownership document',
                    'Engineering plan',
                    'National ID copy',
                ],
                'requires_appointment' => true,
                'issues_certificate' => true,
                'office' => $hamraOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Municipality Services',
                'name' => 'Municipal Complaint',
                'description' => 'Submit a complaint related to local municipal issues.',
                'base_fee' => 0.00,
                'estimated_processing_days' => 5,
                'required_documents' => [
                    'Complaint description',
                    'Optional supporting images or documents',
                ],
                'requires_appointment' => false,
                'issues_certificate' => false,
                'office' => $hamraOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Municipality Services',
                'name' => 'Occupancy Certificate Request',
                'description' => 'Request a certificate confirming property occupancy status.',
                'base_fee' => 15.00,
                'estimated_processing_days' => 7,
                'required_documents' => [
                    'Property ownership document',
                    'Utility bill copy',
                    'National ID copy',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $hamraOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Tax Services',
                'name' => 'Tax Clearance Request',
                'description' => 'Request an official tax clearance certificate.',
                'base_fee' => 20.00,
                'estimated_processing_days' => 5,
                'required_documents' => [
                    'National ID copy',
                    'Tax identification number',
                    'Previous tax declaration if available',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $tripoliOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Tax Services',
                'name' => 'Property Tax Statement',
                'description' => 'Request a property tax statement for a registered property.',
                'base_fee' => 10.00,
                'estimated_processing_days' => 4,
                'required_documents' => [
                    'Property ownership document',
                    'National ID copy',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $tripoliOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Health Services',
                'name' => 'Health Coverage Certificate',
                'description' => 'Request proof of public health coverage eligibility.',
                'base_fee' => 0.00,
                'estimated_processing_days' => 6,
                'required_documents' => [
                    'National ID copy',
                    'Proof of residence',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $beirutOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Health Services',
                'name' => 'Medical Assistance Request',
                'description' => 'Submit a request for public medical assistance review.',
                'base_fee' => 0.00,
                'estimated_processing_days' => 10,
                'required_documents' => [
                    'National ID copy',
                    'Medical report',
                    'Proof of income',
                ],
                'requires_appointment' => true,
                'issues_certificate' => true,
                'office' => $beirutOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Education Services',
                'name' => 'Student Enrollment Certificate',
                'description' => 'Request an official student enrollment certificate.',
                'base_fee' => 3.00,
                'estimated_processing_days' => 2,
                'required_documents' => [
                    'Student ID copy',
                    'National ID copy',
                ],
                'requires_appointment' => false,
                'issues_certificate' => true,
                'office' => $hamraOffice,
                'is_active' => true,
            ],
            [
                'category' => 'Education Services',
                'name' => 'Scholarship Assistance Request',
                'description' => 'Submit a request for public scholarship assistance.',
                'base_fee' => 0.00,
                'estimated_processing_days' => 15,
                'required_documents' => [
                    'Student ID copy',
                    'Academic transcript',
                    'Proof of income',
                    'National ID copy',
                ],
                'requires_appointment' => true,
                'issues_certificate' => true,
                'office' => $hamraOffice,
                'is_active' => true,
            ],
        ];

        foreach ($services as $serviceData) {
            $category = ServiceCategory::where('name', $serviceData['category'])->first();

            if (! $category) {
                continue;
            }

            $office = $serviceData['office'] ?? $beirutOffice;

            Service::updateOrCreate(
                [
                    'name' => $serviceData['name'],
                    'service_category_id' => $category->id,
                ],
                [
                    'office_id' => $office?->id,
                    'description' => $serviceData['description'],
                    'base_fee' => $serviceData['base_fee'],
                    'estimated_processing_days' => $serviceData['estimated_processing_days'],
                    'required_documents' => $serviceData['required_documents'],
                    'requires_appointment' => $serviceData['requires_appointment'],
                    'issues_certificate' => $serviceData['issues_certificate'] ?? true,
                    'is_active' => $serviceData['is_active'],
                    'image_url' => $this->serviceImageUrl($serviceData['name']),
                ]
            );
        }
    }
}
