<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminMunicipalityTest extends TestCase
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
    public function admin_can_list_create_update_and_delete_municipalities(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/admin/municipalities')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');

        $this->postJson('/api/admin/municipalities', [
            'name' => 'Beirut Municipality',
            'code' => 'BEY',
            'address' => 'Municipality Square',
            'phone' => '+96111223344',
            'email' => 'info@beirut.gov.lb',
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Beirut Municipality')
            ->assertJsonPath('data.code', 'BEY')
            ->assertJsonPath('data.is_active', true);

        $municipality = Municipality::query()->where('code', 'BEY')->first();
        $this->assertNotNull($municipality);

        $this->patchJson("/api/admin/municipalities/{$municipality->id}", [
            'name' => 'Beirut City',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Beirut City')
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/admin/municipalities/{$municipality->id}")
            ->assertOk();

        $this->assertDatabaseMissing('municipalities', ['id' => $municipality->id]);
    }

    #[Test]
    public function non_admin_cannot_manage_municipalities(): void
    {
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $municipality = Municipality::query()->create([
            'name' => 'Tripoli',
            'is_active' => true,
        ]);

        $this->actingAs($citizen);

        $this->getJson('/api/admin/municipalities')->assertForbidden();
        $this->postJson('/api/admin/municipalities', ['name' => 'New'])->assertForbidden();
        $this->patchJson("/api/admin/municipalities/{$municipality->id}", ['name' => 'Updated'])
            ->assertForbidden();
        $this->deleteJson("/api/admin/municipalities/{$municipality->id}")->assertForbidden();
    }

    #[Test]
    public function municipality_code_must_be_unique(): void
    {
        $this->actingAsAdmin();

        Municipality::query()->create([
            'name' => 'Existing',
            'code' => 'DUPLICATE',
            'is_active' => true,
        ]);

        $this->postJson('/api/admin/municipalities', [
            'name' => 'Another',
            'code' => 'DUPLICATE',
        ])->assertStatus(422);
    }

    #[Test]
    public function admin_cannot_delete_municipality_with_offices(): void
    {
        $this->actingAsAdmin();

        $municipality = Municipality::query()->create([
            'name' => 'Sidon',
            'is_active' => true,
        ]);

        Office::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Sidon Office',
            'address' => 'Downtown',
            'is_active' => true,
        ]);

        $this->deleteJson("/api/admin/municipalities/{$municipality->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Municipality has offices attached.');

        $this->assertDatabaseHas('municipalities', ['id' => $municipality->id]);
    }
}
