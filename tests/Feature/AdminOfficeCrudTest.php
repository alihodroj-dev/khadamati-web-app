<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminOfficeCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    #[Test]
    public function admin_can_list_show_create_update_and_delete_offices(): void
    {
        $this->actingAsAdmin();

        $municipality = Municipality::query()->create([
            'name' => 'Beirut',
            'code' => 'BEY',
            'is_active' => true,
        ]);

        $this->getJson('/api/admin/offices')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->postJson('/api/admin/offices', [
            'municipality_id' => $municipality->id,
            'name' => 'Downtown Office',
            'address' => 'Hamra Street',
            'phone' => '+96111223344',
            'email' => 'downtown@example.com',
            'latitude' => 33.8938,
            'longitude' => 35.5018,
            'working_hours' => ['monday' => ['09:00', '17:00']],
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Downtown Office')
            ->assertJsonPath('data.municipality.name', 'Beirut');

        $office = Office::query()->where('name', 'Downtown Office')->first();
        $this->assertNotNull($office);

        $this->getJson('/api/admin/offices')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/admin/offices/{$office->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $office->id)
            ->assertJsonPath('data.address', 'Hamra Street');

        $this->patchJson("/api/admin/offices/{$office->id}", [
            'name' => 'Hamra Office',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Hamra Office')
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/admin/offices/{$office->id}")
            ->assertOk();

        $this->assertDatabaseMissing('offices', ['id' => $office->id]);
    }

    #[Test]
    public function admin_can_deactivate_office_instead_of_deleting_when_requests_exist(): void
    {
        $this->actingAsAdmin();

        $office = $this->createOfficeWithServiceRequest();

        $this->deleteJson("/api/admin/offices/{$office->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Office has service requests attached.');

        $this->patchJson("/api/admin/offices/{$office->id}", [
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function office_latitude_and_longitude_must_be_within_valid_ranges(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/admin/offices', [
            'name' => 'Invalid Coordinates Office',
            'address' => 'Somewhere',
            'latitude' => 95,
            'longitude' => 200,
            'is_active' => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    #[Test]
    public function non_admin_cannot_manage_offices_via_api(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $office = Office::query()->create([
            'name' => 'Public Office',
            'address' => 'Main',
            'is_active' => true,
        ]);

        $this->actingAs($citizen);

        $this->getJson('/api/admin/offices')->assertForbidden();
        $this->postJson('/api/admin/offices', [
            'name' => 'New',
            'address' => 'Addr',
        ])->assertForbidden();
        $this->getJson("/api/admin/offices/{$office->id}")->assertForbidden();
        $this->patchJson("/api/admin/offices/{$office->id}", ['name' => 'Updated'])->assertForbidden();
        $this->deleteJson("/api/admin/offices/{$office->id}")->assertForbidden();
    }

    private function createOfficeWithServiceRequest(): Office
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $office = Office::query()->create([
            'name' => 'Busy Office',
            'address' => 'Center',
            'is_active' => true,
        ]);

        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => 'Certificate',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-20260517-TEST01',
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'submitted',
        ]);

        return $office;
    }
}
