<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_offices_by_service_id_for_office_specific_service(): void
    {
        $category = $this->createCategory();
        $officeA = $this->createOffice('Office A', 33.9, 35.5);
        $officeB = $this->createOffice('Office B', 34.0, 35.6);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $officeA->id,
            'name' => 'Office-only service',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/offices?service_id={$service->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.offices')
            ->assertJsonPath('data.offices.0.id', $officeA->id);
    }

    public function test_global_service_returns_all_active_offices(): void
    {
        $category = $this->createCategory();
        $officeA = $this->createOffice('Office A', 33.9, 35.5);
        $this->createOffice('Office B', 34.0, 35.6);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => null,
            'name' => 'Global service',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/offices?service_id={$service->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data.offices');
    }

    public function test_filters_offices_by_category_id(): void
    {
        $categoryA = $this->createCategory('Category A');
        $categoryB = $this->createCategory('Category B');
        $officeA = $this->createOffice('Office A', 33.9, 35.5);
        $officeB = $this->createOffice('Office B', 34.0, 35.6);

        Service::query()->create([
            'service_category_id' => $categoryA->id,
            'office_id' => $officeA->id,
            'name' => 'Service A',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        Service::query()->create([
            'service_category_id' => $categoryB->id,
            'office_id' => $officeB->id,
            'name' => 'Service B',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/offices?category_id={$categoryA->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.offices')
            ->assertJsonPath('data.offices.0.id', $officeA->id);
    }

    public function test_sorts_by_distance_and_includes_distance_km(): void
    {
        $near = $this->createOffice('Near Office', 33.9000, 35.5000);
        $far = $this->createOffice('Far Office', 34.5000, 36.0000);

        $response = $this->getJson('/api/offices?near_lat=33.8938&near_lng=35.5018');

        $response->assertOk()
            ->assertJsonPath('data.offices.0.id', $near->id)
            ->assertJsonPath('data.offices.1.id', $far->id)
            ->assertJsonStructure([
                'data' => [
                    'offices' => [
                        ['distance_km'],
                    ],
                ],
            ]);

        $this->assertLessThan(
            $response->json('data.offices.1.distance_km'),
            $response->json('data.offices.0.distance_km')
        );
    }

    public function test_search_filters_office_name_and_address(): void
    {
        $this->createOffice('Beirut Main', 33.9, 35.5, 'Hamra Street');
        $this->createOffice('Tripoli Branch', 34.4, 35.8, 'Downtown');

        $response = $this->getJson('/api/offices?search=Hamra');

        $response->assertOk()
            ->assertJsonCount(1, 'data.offices')
            ->assertJsonPath('data.offices.0.name', 'Beirut Main');
    }

    private function createCategory(string $name = 'Civil'): ServiceCategory
    {
        return ServiceCategory::query()->create([
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function createOffice(
        string $name,
        float $latitude,
        float $longitude,
        string $address = 'Address'
    ): Office {
        return Office::query()->create([
            'name' => $name,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_active' => true,
        ]);
    }
}
