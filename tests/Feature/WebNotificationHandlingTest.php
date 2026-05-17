<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdatedNotification;
use App\Support\WebNotificationHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebNotificationHandlingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_view_notifications_index(): void
    {
        $staff = $this->createStaffUser();
        $serviceRequest = $this->createServiceRequestForStaff($staff);

        $staff->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request updated',
            'A citizen request needs review.'
        ));

        $this->actingAs($staff)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Request updated')
            ->assertSee('A citizen request needs review')
            ->assertSee('1 unread');
    }

    #[Test]
    public function user_can_mark_one_notification_as_read(): void
    {
        $staff = $this->createStaffUser();
        $serviceRequest = $this->createServiceRequestForStaff($staff);

        $staff->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request updated',
            'Needs review.'
        ));

        $notification = $staff->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);

        $this->actingAs($staff)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('staff.requests.show', $serviceRequest->id))
            ->assertSessionHas('success');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function user_can_mark_all_notifications_as_read(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $admin->notify(new RequestUpdatedNotification(
            $this->createBareServiceRequest(),
            'First',
            'First body'
        ));
        $admin->notify(new RequestUpdatedNotification(
            $this->createBareServiceRequest(),
            'Second',
            'Second body'
        ));

        $this->assertSame(2, $admin->unreadNotifications()->count());

        $this->actingAs($admin)
            ->from(route('notifications.index'))
            ->post(route('notifications.readAll'))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success');

        $this->assertSame(0, $admin->unreadNotifications()->count());
    }

    #[Test]
    public function user_cannot_mark_another_users_notification(): void
    {
        $staffA = $this->createStaffUser('staff-a@test.com');
        $staffB = $this->createStaffUser('staff-b@test.com');

        $request = $this->createServiceRequestForStaff($staffA);
        $staffA->notify(new RequestUpdatedNotification($request, 'Private', 'Only for A'));

        $notificationId = $staffA->notifications()->first()->id;

        $this->actingAs($staffB)
            ->post(route('notifications.read', $notificationId))
            ->assertNotFound();

        $this->assertNull(DatabaseNotification::find($notificationId)?->read_at);
    }

    #[Test]
    public function web_notification_helper_builds_staff_request_url(): void
    {
        $staff = $this->createStaffUser();
        $serviceRequest = $this->createServiceRequestForStaff($staff);

        $staff->notify(new RequestUpdatedNotification(
            $serviceRequest,
            'Request updated',
            'Review needed.'
        ));

        $formatted = app(WebNotificationHelper::class)->format(
            $staff->notifications()->first(),
            $staff
        );

        $this->assertSame(route('staff.requests.show', $serviceRequest->id), $formatted['url']);
        $this->assertTrue($formatted['is_unread']);
        $this->assertSame('ti-clipboard-list', $formatted['icon']);
    }

    #[Test]
    public function guest_is_redirected_from_notifications(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    private function createStaffUser(string $email = 'staff@test.com'): User
    {
        $office = Office::query()->create([
            'name' => 'Main Office',
            'address' => 'Center',
            'is_active' => true,
        ]);

        return User::factory()->create([
            'role' => User::ROLE_STAFF,
            'office_id' => $office->id,
            'email' => $email,
            'is_active' => true,
        ]);
    }

    private function createServiceRequestForStaff(User $staff): ServiceRequest
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Civil',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $staff->office_id,
            'name' => 'Permit',
            'base_fee' => 10,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $staff->office_id,
            'reference_number' => 'KHR-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'under_review',
        ]);
    }

    private function createBareServiceRequest(): ServiceRequest
    {
        $category = ServiceCategory::query()->create([
            'name' => 'General',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Service',
            'base_fee' => 0,
            'is_active' => true,
        ]);

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'reference_number' => 'KHR-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => 'pending',
        ]);
    }
}
