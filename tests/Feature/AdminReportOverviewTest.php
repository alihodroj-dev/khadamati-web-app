<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminReportOverviewTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_fetch_report_overview(): void
    {
        $admin = $this->actingAsAdmin();

        $this->getJson('/api/admin/reports/overview')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'filters',
                    'summary' => [
                        'total_requests',
                        'total_revenue',
                        'payment_counts' => ['pending', 'paid', 'refunded', 'failed'],
                    ],
                    'requests_by_status',
                    'requests_by_office',
                    'revenue_by_office',
                    'requests_by_date',
                    'revenue_by_date',
                    'top_services',
                    'by_municipality',
                ],
            ]);
    }

    #[Test]
    public function overview_calculates_requests_and_revenue_per_office(): void
    {
        $this->actingAsAdmin();

        $municipality = Municipality::query()->create([
            'name' => 'Beirut',
            'code' => 'BEY',
            'is_active' => true,
        ]);

        $officeA = $this->createOffice('Office A', $municipality);
        $officeB = $this->createOffice('Office B', $municipality);

        $serviceA = $this->createService($officeA, 'Permit A');
        $serviceB = $this->createService($officeB, 'Permit B');

        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $requestA = $this->createRequest($citizen, $serviceA, $officeA, 'completed');
        $requestB = $this->createRequest($citizen, $serviceB, $officeB, 'pending');
        $this->createRequest($citizen, $serviceB, $officeB, 'pending');

        $this->createPaidPayment($requestA, 100.00, '2026-05-10');
        $this->createPaidPayment($requestB, 50.00, '2026-05-11');
        $this->createPayment($requestB, 25.00, 'pending');

        $response = $this->getJson('/api/admin/reports/overview')->assertOk();

        $requestsByOffice = collect($response->json('data.requests_by_office'))->keyBy('office_id');
        $revenueByOffice = collect($response->json('data.revenue_by_office'))->keyBy('office_id');

        $this->assertSame(1, $requestsByOffice[$officeA->id]['count']);
        $this->assertSame(2, $requestsByOffice[$officeB->id]['count']);
        $this->assertEquals(100.0, $revenueByOffice[$officeA->id]['revenue']);
        $this->assertEquals(50.0, $revenueByOffice[$officeB->id]['revenue']);

        $this->assertEquals(150.0, $response->json('data.summary.total_revenue'));
        $this->assertSame(3, $response->json('data.summary.total_requests'));
        $this->assertSame(1, $response->json('data.summary.payment_counts.pending'));
        $this->assertSame(2, $response->json('data.summary.payment_counts.paid'));
    }

    #[Test]
    public function overview_includes_request_counts_by_status(): void
    {
        $this->actingAsAdmin();

        $office = $this->createOffice('Main');
        $service = $this->createService($office, 'License');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $this->createRequest($citizen, $service, $office, 'pending');
        $this->createRequest($citizen, $service, $office, 'pending');
        $this->createRequest($citizen, $service, $office, 'completed');

        $statusCounts = collect(
            $this->getJson('/api/admin/reports/overview')
                ->assertOk()
                ->json('data.requests_by_status')
        )->pluck('count', 'status');

        $this->assertSame(2, $statusCounts['pending']);
        $this->assertSame(1, $statusCounts['completed']);
        $this->assertSame(0, $statusCounts['rejected']);
    }

    #[Test]
    public function overview_filters_requests_by_date_range(): void
    {
        $this->actingAsAdmin();

        $office = $this->createOffice('Main');
        $service = $this->createService($office, 'License');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $old = $this->createRequest($citizen, $service, $office, 'pending');
        $old->forceFill(['created_at' => '2026-05-01 10:00:00'])->save();

        $recent = $this->createRequest($citizen, $service, $office, 'pending');
        $recent->forceFill(['created_at' => '2026-05-15 10:00:00'])->save();

        $response = $this->getJson('/api/admin/reports/overview?from_date=2026-05-10&to_date=2026-05-20')
            ->assertOk();

        $this->assertSame(1, $response->json('data.summary.total_requests'));
        $this->assertCount(1, $response->json('data.requests_by_date'));
    }

    #[Test]
    public function overview_filters_revenue_by_paid_at_date_range(): void
    {
        $this->actingAsAdmin();

        $office = $this->createOffice('Main');
        $service = $this->createService($office, 'License');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $requestInRange = $this->createRequest($citizen, $service, $office, 'completed');
        $requestOutOfRange = $this->createRequest($citizen, $service, $office, 'completed');

        $this->createPaidPayment($requestInRange, 80.00, '2026-05-12');
        $this->createPaidPayment($requestOutOfRange, 200.00, '2026-04-01');

        $response = $this->getJson('/api/admin/reports/overview?from_date=2026-05-01&to_date=2026-05-31')
            ->assertOk();

        $this->assertEquals(80.0, $response->json('data.summary.total_revenue'));
        $this->assertCount(1, $response->json('data.revenue_by_date'));
        $this->assertEquals(80.0, $response->json('data.revenue_by_date.0.amount'));
    }

    #[Test]
    public function overview_filters_by_office_and_municipality(): void
    {
        $this->actingAsAdmin();

        $municipalityA = Municipality::query()->create(['name' => 'A', 'code' => 'A', 'is_active' => true]);
        $municipalityB = Municipality::query()->create(['name' => 'B', 'code' => 'B', 'is_active' => true]);

        $officeA = $this->createOffice('Office A', $municipalityA);
        $officeB = $this->createOffice('Office B', $municipalityB);

        $serviceA = $this->createService($officeA, 'Svc A');
        $serviceB = $this->createService($officeB, 'Svc B');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $requestA = $this->createRequest($citizen, $serviceA, $officeA, 'completed');
        $this->createRequest($citizen, $serviceB, $officeB, 'completed');
        $this->createPaidPayment($requestA, 40.00, '2026-05-10');

        $byOffice = $this->getJson("/api/admin/reports/overview?office_id={$officeA->id}")
            ->assertOk();

        $this->assertSame(1, $byOffice->json('data.summary.total_requests'));
        $this->assertEquals(40.0, $byOffice->json('data.summary.total_revenue'));

        $byMunicipality = $this->getJson("/api/admin/reports/overview?municipality_id={$municipalityA->id}")
            ->assertOk();

        $this->assertSame(1, $byMunicipality->json('data.summary.total_requests'));
        $this->assertCount(1, $byMunicipality->json('data.by_municipality'));
        $this->assertSame('A', $byMunicipality->json('data.by_municipality.0.municipality_name'));
    }

    #[Test]
    public function overview_lists_top_services_by_request_count(): void
    {
        $this->actingAsAdmin();

        $office = $this->createOffice('Main');
        $popular = $this->createService($office, 'Popular');
        $rare = $this->createService($office, 'Rare');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);

        $this->createRequest($citizen, $popular, $office, 'pending');
        $this->createRequest($citizen, $popular, $office, 'pending');
        $this->createRequest($citizen, $rare, $office, 'pending');

        $top = $this->getJson('/api/admin/reports/overview')
            ->assertOk()
            ->json('data.top_services');

        $this->assertSame('Popular', $top[0]['service_name']);
        $this->assertSame(2, $top[0]['request_count']);
        $this->assertSame('Rare', $top[1]['service_name']);
    }

    #[Test]
    public function pending_payments_do_not_count_toward_revenue(): void
    {
        $this->actingAsAdmin();

        $office = $this->createOffice('Main');
        $service = $this->createService($office, 'License');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $request = $this->createRequest($citizen, $service, $office, 'completed');

        $this->createPayment($request, 500.00, 'pending');

        $this->getJson('/api/admin/reports/overview')
            ->assertOk()
            ->assertJsonPath('data.summary.total_revenue', 0)
            ->assertJsonCount(0, 'data.revenue_by_office');
    }

    #[Test]
    public function non_admin_cannot_access_report_overview(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        $this->getJson('/api/admin/reports/overview')->assertForbidden();
    }

    #[Test]
    public function admin_reports_page_displays_filtered_metrics(): void
    {
        $admin = $this->actingAsAdmin();

        $office = $this->createOffice('Web Office');
        $service = $this->createService($office, 'Web Service');
        $citizen = User::factory()->create(['role' => User::ROLE_CITIZEN]);
        $request = $this->createRequest($citizen, $service, $office, 'completed');
        $this->createPaidPayment($request, 75.50, '2026-05-10');

        $this->get(route('admin.reports.index', ['office_id' => $office->id]))
            ->assertOk()
            ->assertSee('Web Office')
            ->assertSee('$75.50')
            ->assertSee('Total Revenue');
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    private function createOffice(string $name, ?Municipality $municipality = null): Office
    {
        return Office::query()->create([
            'municipality_id' => $municipality?->id,
            'name' => $name,
            'address' => 'Main Street',
            'is_active' => true,
        ]);
    }

    private function createService(Office $office, string $name): Service
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Category '.uniqid(),
            'is_active' => true,
        ]);

        return Service::query()->create([
            'service_category_id' => $category->id,
            'office_id' => $office->id,
            'name' => $name,
            'base_fee' => 10,
            'is_active' => true,
        ]);
    }

    private function createRequest(
        User $citizen,
        Service $service,
        Office $office,
        string $status
    ): ServiceRequest {
        return ServiceRequest::query()->create([
            'user_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'reference_number' => 'KHR-'.uniqid(),
            'tracking_token' => ServiceRequest::generateTrackingToken(),
            'status' => $status,
        ]);
    }

    private function createPaidPayment(
        ServiceRequest $request,
        float $amount,
        string $paidDate
    ): Payment {
        return $this->createPayment($request, $amount, 'paid', $paidDate);
    }

    private function createPayment(
        ServiceRequest $request,
        float $amount,
        string $status,
        ?string $paidDate = null
    ): Payment {
        return Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $request->user_id,
            'amount' => $amount,
            'currency' => 'USD',
            'payment_method' => 'cash',
            'status' => $status,
            'transaction_reference' => 'PAY-'.uniqid(),
            'paid_at' => $status === 'paid' ? $paidDate.' 12:00:00' : null,
        ]);
    }
}
