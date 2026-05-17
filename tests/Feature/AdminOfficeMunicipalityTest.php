<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminOfficeMunicipalityTest extends TestCase
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
    public function admin_can_create_office_with_municipality_assignment(): void
    {
        $this->actingAsAdmin();

        $municipality = Municipality::query()->create([
            'name' => 'Jounieh',
            'code' => 'JOU',
            'is_active' => true,
        ]);

        $this->postJson('/api/admin/offices', [
            'municipality_id' => $municipality->id,
            'name' => 'Jounieh Branch',
            'address' => 'Main Road',
            'phone' => '+96170000000',
            'email' => 'jounieh@example.com',
            'latitude' => 33.98,
            'longitude' => 35.62,
            'working_hours' => ['monday' => ['09:00', '17:00']],
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.municipality_id', $municipality->id)
            ->assertJsonPath('data.municipality.id', $municipality->id)
            ->assertJsonPath('data.municipality.name', 'Jounieh')
            ->assertJsonPath('data.name', 'Jounieh Branch');

        $this->assertDatabaseHas('offices', [
            'name' => 'Jounieh Branch',
            'municipality_id' => $municipality->id,
        ]);
    }

    #[Test]
    public function admin_can_update_office_municipality_assignment(): void
    {
        $this->actingAsAdmin();

        $originalMunicipality = Municipality::query()->create([
            'name' => 'Byblos',
            'is_active' => true,
        ]);

        $newMunicipality = Municipality::query()->create([
            'name' => 'Batroun',
            'is_active' => true,
        ]);

        $office = Office::query()->create([
            'municipality_id' => $originalMunicipality->id,
            'name' => 'Coastal Office',
            'address' => 'Harbor',
            'is_active' => true,
        ]);

        $this->patchJson("/api/admin/offices/{$office->id}", [
            'municipality_id' => $newMunicipality->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.municipality_id', $newMunicipality->id)
            ->assertJsonPath('data.municipality.name', 'Batroun');

        $office->refresh();
        $this->assertSame($newMunicipality->id, $office->municipality_id);
    }

    #[Test]
    public function admin_can_detach_office_from_municipality(): void
    {
        $this->actingAsAdmin();

        $municipality = Municipality::query()->create([
            'name' => 'Zahle',
            'is_active' => true,
        ]);

        $office = Office::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Zahle Office',
            'address' => 'Center',
            'is_active' => true,
        ]);

        $this->patchJson("/api/admin/offices/{$office->id}", [
            'municipality_id' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.municipality_id', null);

        $office->refresh();
        $this->assertNull($office->municipality_id);
    }

    #[Test]
    public function public_office_endpoint_includes_municipality_when_loaded(): void
    {
        $municipality = Municipality::query()->create([
            'name' => 'Tyre',
            'code' => 'TYR',
            'is_active' => true,
        ]);

        $office = Office::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Tyre Office',
            'address' => 'Corniche',
            'is_active' => true,
        ]);

        $office->load('municipality');

        $this->getJson("/api/offices/{$office->id}")
            ->assertOk()
            ->assertJsonPath('data.office.municipality_id', $municipality->id)
            ->assertJsonPath('data.office.municipality.name', 'Tyre')
            ->assertJsonPath('data.office.municipality.code', 'TYR');
    }
}
