<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffOfficeProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function staff_can_view_assigned_office_profile(): void
    {
        $office = $this->createOffice('Main Office');
        $staff = $this->createStaffForOffice($office);

        $this->actingAs($staff);

        $this->getJson('/api/staff/office')
            ->assertOk()
            ->assertJsonPath('data.id', $office->id)
            ->assertJsonPath('data.name', 'Main Office')
            ->assertJsonPath('data.address', 'Main Street');
    }

    #[Test]
    public function staff_can_update_assigned_office_details(): void
    {
        $office = $this->createOffice('Main Office');
        $staff = $this->createStaffForOffice($office);

        $this->actingAs($staff);

        $this->patchJson('/api/staff/office', [
            'name' => 'Updated Office',
            'address' => 'New Address 12',
            'phone' => '+96170000000',
            'email' => 'office@example.com',
            'latitude' => 33.9,
            'longitude' => 35.5,
            'working_hours' => [
                'monday' => ['09:00', '17:00'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Office')
            ->assertJsonPath('data.phone', '+96170000000')
            ->assertJsonPath('data.latitude', 33.9)
            ->assertJsonPath('data.longitude', 35.5);

        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'name' => 'Updated Office',
            'address' => 'New Address 12',
            'phone' => '+96170000000',
            'email' => 'office@example.com',
        ]);
    }

    #[Test]
    public function staff_without_assigned_office_cannot_access_office_profile(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $this->getJson('/api/staff/office')->assertForbidden();
        $this->patchJson('/api/staff/office', ['name' => 'Test'])->assertForbidden();
    }

    #[Test]
    public function admin_and_citizen_cannot_use_staff_office_routes(): void
    {
        $office = $this->createOffice('Main Office');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->getJson('/api/staff/office')->assertForbidden();

        $this->actingAs($citizen);
        $this->getJson('/api/staff/office')->assertForbidden();

        $staff = $this->createStaffForOffice($office);
        $this->actingAs($staff);
        $this->getJson('/api/staff/office')->assertOk();
    }

    #[Test]
    public function staff_cannot_update_municipality_or_active_status_via_api(): void
    {
        $office = $this->createOffice('Main Office');
        $staff = $this->createStaffForOffice($office);

        $this->actingAs($staff);

        $this->patchJson('/api/staff/office', [
            'name' => 'Renamed Office',
            'municipality_id' => 999,
            'is_active' => false,
        ])->assertOk();

        $office->refresh();

        $this->assertSame('Renamed Office', $office->name);
        $this->assertNull($office->municipality_id);
        $this->assertTrue($office->is_active);
    }

    #[Test]
    public function staff_office_update_rejects_invalid_coordinates(): void
    {
        $office = $this->createOffice('Main Office');
        $staff = $this->createStaffForOffice($office);

        $this->actingAs($staff);

        $this->patchJson('/api/staff/office', [
            'latitude' => 95,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);

        $this->patchJson('/api/staff/office', [
            'longitude' => 200,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['longitude']);
    }

    #[Test]
    public function staff_from_one_office_only_updates_their_own_office(): void
    {
        $officeA = $this->createOffice('Office A');
        $officeB = $this->createOffice('Office B');

        $staffA = $this->createStaffForOffice($officeA);

        $this->actingAs($staffA);

        $this->patchJson('/api/staff/office', [
            'name' => 'Office A Updated',
        ])->assertOk();

        $officeA->refresh();
        $officeB->refresh();

        $this->assertSame('Office A Updated', $officeA->name);
        $this->assertSame('Office B', $officeB->name);
    }

    #[Test]
    public function staff_can_update_office_profile_via_web_form(): void
    {
        $office = $this->createOffice('Main Office');
        $staff = $this->createStaffForOffice($office);

        $this->actingAs($staff);

        $this->put(route('staff.office.update'), [
            'name' => 'Web Updated Office',
            'address' => 'Web Street 1',
            'phone' => '+96111111111',
            'email' => 'web@office.test',
            'latitude' => '33.8888',
            'longitude' => '35.4444',
            'working_hours' => '{"tuesday":["10:00","16:00"]}',
        ])
            ->assertRedirect(route('staff.office.edit'))
            ->assertSessionHas('success');

        $office->refresh();

        $this->assertSame('Web Updated Office', $office->name);
        $this->assertSame('web@office.test', $office->email);
        $this->assertEquals(33.8888, (float) $office->latitude);
        $this->assertSame(['tuesday' => ['10:00', '16:00']], $office->working_hours);
    }

    #[Test]
    public function admin_can_still_update_any_office_via_admin_routes(): void
    {
        $office = $this->createOffice('Admin Target');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $this->patchJson("/api/admin/offices/{$office->id}", [
            'name' => 'Admin Renamed',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Admin Renamed')
            ->assertJsonPath('data.is_active', false);
    }

    private function createOffice(string $name): Office
    {
        return Office::query()->create([
            'name' => $name,
            'address' => 'Main Street',
            'is_active' => true,
        ]);
    }

    private function createStaffForOffice(Office $office): User
    {
        return User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => true,
        ]);
    }
}
