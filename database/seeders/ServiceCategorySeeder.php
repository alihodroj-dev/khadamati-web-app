<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Civil Records',
                'description' => 'Services related to personal civil documents and official records.',
                'icon' => 'file-text',
                'is_active' => true,
            ],
            [
                'name' => 'Municipality Services',
                'description' => 'Local municipality services such as permits, complaints, and certificates.',
                'icon' => 'building',
                'is_active' => true,
            ],
            [
                'name' => 'Tax Services',
                'description' => 'Tax-related requests, clearances, and official declarations.',
                'icon' => 'receipt',
                'is_active' => true,
            ],
            [
                'name' => 'Health Services',
                'description' => 'Health-related public services and medical administrative requests.',
                'icon' => 'heart-pulse',
                'is_active' => true,
            ],
            [
                'name' => 'Education Services',
                'description' => 'Student, school, university, and education-related administrative services.',
                'icon' => 'graduation-cap',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
