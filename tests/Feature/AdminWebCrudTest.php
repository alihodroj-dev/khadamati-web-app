<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
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
        $this->assertSame(['national_id', 'utility_bill'], $service->required_documents);

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

        $this->post(route('admin.users.store'), [
            'name' => 'Jane Citizen',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'phone' => '96170000000',
            'national_id' => 'NID-100',
            'role' => User::ROLE_CITIZEN,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
            'phone' => '96171111111',
            'national_id' => 'NID-100',
            'role' => User::ROLE_STAFF,
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
}
