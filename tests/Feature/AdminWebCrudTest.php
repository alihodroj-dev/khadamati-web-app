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

class AdminWebCrudTest extends TestCase
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
    public function admin_can_create_update_and_delete_a_category(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.categories.store'), [
            'name' => 'Civil Services',
            'description' => 'Government paperwork',
            'icon' => 'building',
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $category = ServiceCategory::query()->where('name', 'Civil Services')->first();
        $this->assertNotNull($category);
        $this->assertTrue($category->is_active);

        $this->put(route('admin.categories.update', $category), [
            'name' => 'Civil Registry',
            'description' => 'Updated',
            'icon' => 'id-card',
            'is_active' => '0',
        ])->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Civil Registry', $category->name);
        $this->assertFalse($category->is_active);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
    }

    #[Test]
    public function admin_cannot_delete_category_with_services(): void
    {
        $this->actingAsAdmin();

        $category = ServiceCategory::query()->create([
            'name' => 'Health',
            'is_active' => true,
        ]);

        Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Medical Certificate',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('service_categories', ['id' => $category->id]);
    }

    #[Test]
    public function admin_can_create_update_and_delete_a_service(): void
    {
        $this->actingAsAdmin();

        $category = ServiceCategory::query()->create([
            'name' => 'Utilities',
            'is_active' => true,
        ]);

        $this->post(route('admin.services.store'), [
            'service_category_id' => $category->id,
            'name' => 'Water Connection',
            'description' => 'New connection request',
            'base_fee' => '25.50',
            'estimated_processing_days' => '14',
            'required_documents' => 'national_id, utility_bill',
            'requires_appointment' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('admin.services.index'));

        $service = Service::query()->where('name', 'Water Connection')->first();
        $this->assertNotNull($service);
        $this->assertCount(2, $service->required_documents);
        $this->assertSame('national_id', $service->required_documents[0]['key']);
        $this->assertSame('utility_bill', $service->required_documents[1]['key']);

        $this->put(route('admin.services.update', $service), [
            'service_category_id' => $category->id,
            'name' => 'Water Reconnection',
            'description' => 'Reconnect service',
            'base_fee' => '30',
            'estimated_processing_days' => '7',
            'required_documents' => 'national_id',
            'requires_appointment' => '0',
            'is_active' => '1',
        ])->assertRedirect(route('admin.services.index'));

        $service->refresh();
        $this->assertSame('Water Reconnection', $service->name);
        $this->assertFalse($service->requires_appointment);

        $this->delete(route('admin.services.destroy', $service))
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    #[Test]
    public function admin_can_create_update_and_delete_users(): void
    {
        $this->actingAsAdmin();

        $office = Office::query()->create([
            'name' => 'Main Office',
            'address' => 'Center',
            'is_active' => true,
        ]);

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Citizen',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'phone' => '96170000000',
            'national_id' => 'NID-100',
            'role' => User::ROLE_CITIZEN,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->post(route('admin.users.store'), [
            'name' => 'Staff Member',
            'email' => 'staff.member@example.com',
            'password' => 'password123',
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $staff = User::query()->where('email', 'staff.member@example.com')->first();
        $this->assertNotNull($staff);
        $this->assertSame($office->id, $staff->office_id);

        $user = User::query()->where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
            'phone' => '96171111111',
            'national_id' => 'NID-100',
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'is_active' => '0',
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('Jane Updated', $user->name);
        $this->assertSame(User::ROLE_STAFF, $user->role);
        $this->assertFalse($user->is_active);

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    public function admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->actingAsAdmin();

        $this->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    #[Test]
    public function staff_user_without_office_returns_validation_error_instead_of_crashing(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => null,
            'is_active' => true,
        ]);

        $this->from(route('admin.users.edit', $user))->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => User::ROLE_STAFF,
            'office_id' => '',
            'is_active' => '0',
        ])->assertRedirect(route('admin.users.edit', $user))
            ->assertSessionHasErrors('office_id');
    }

    #[Test]
    public function admin_can_create_update_and_delete_an_office(): void
    {
        $this->actingAsAdmin();

        $municipality = Municipality::query()->create([
            'name' => 'Jbeil',
            'is_active' => true,
        ]);

        $this->post(route('admin.offices.store'), [
            'municipality_id' => $municipality->id,
            'name' => 'Jbeil Office',
            'address' => 'Old Souk',
            'phone' => '96170000000',
            'email' => 'jbeil@example.com',
            'latitude' => '34.0',
            'longitude' => '35.6',
            'working_hours' => '{"monday":["09:00","17:00"]}',
            'is_active' => '1',
        ])->assertRedirect(route('admin.offices.index'));

        $office = Office::query()->where('name', 'Jbeil Office')->first();
        $this->assertNotNull($office);
        $this->assertSame($municipality->id, $office->municipality_id);
        $this->assertSame(['monday' => ['09:00', '17:00']], $office->working_hours);

        $this->put(route('admin.offices.update', $office), [
            'municipality_id' => '',
            'name' => 'Jbeil Main Office',
            'address' => 'Harbor Road',
            'phone' => '96171111111',
            'email' => 'main@example.com',
            'latitude' => '34.01',
            'longitude' => '35.61',
            'working_hours' => '',
            'is_active' => '0',
        ])->assertRedirect(route('admin.offices.index'));

        $office->refresh();
        $this->assertSame('Jbeil Main Office', $office->name);
        $this->assertNull($office->municipality_id);
        $this->assertFalse($office->is_active);

        $this->delete(route('admin.offices.destroy', $office))
            ->assertRedirect(route('admin.offices.index'));

        $this->assertDatabaseMissing('offices', ['id' => $office->id]);
    }

    #[Test]
    public function admin_cannot_delete_office_with_service_requests(): void
    {
        $this->actingAsAdmin();

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
            'name' => 'Permit',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-20260517-WEB01',
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'submitted',
        ]);

        $this->delete(route('admin.offices.destroy', $office))
            ->assertRedirect(route('admin.offices.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('offices', ['id' => $office->id]);
    }

    #[Test]
    public function office_form_rejects_invalid_working_hours_json(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.offices.store'), [
            'name' => 'Invalid JSON Office',
            'address' => 'Somewhere',
            'working_hours' => 'not-json',
            'is_active' => '1',
        ])->assertSessionHasErrors('working_hours');
    }
}
