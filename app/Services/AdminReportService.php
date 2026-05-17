<?php

namespace App\Services;

use App\Models\Office;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Support\ReportFilters;
use App\Support\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function __construct(
        private readonly ReportFilters $filters,
    ) {}

    public static function overview(ReportFilters $filters): array
    {
        return (new self($filters))->build();
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'filters' => $this->filters->toArray(),
            'summary' => $this->summary(),
            'requests_by_status' => $this->requestsByStatus(),
            'requests_by_office' => $this->requestsByOffice(),
            'revenue_by_office' => $this->revenueByOffice(),
            'requests_by_date' => $this->requestsByDate(),
            'revenue_by_date' => $this->revenueByDate(),
            'top_services' => $this->topServices(),
            'by_municipality' => $this->byMunicipality(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(): array
    {
        $requestQuery = $this->scopedServiceRequestQuery();
        $paymentQuery = $this->scopedPaymentQuery();
        $paidRevenueQuery = $this->scopedPaidPaymentQuery();

        $paymentCounts = (clone $paymentQuery)
            ->select('payments.status', DB::raw('COUNT(*) as count'))
            ->groupBy('payments.status')
            ->pluck('count', 'status');

        return [
            'total_requests' => (int) (clone $requestQuery)->count(),
            'total_revenue' => round((float) (clone $paidRevenueQuery)->sum('amount'), 2),
            'payment_counts' => [
                'pending' => (int) ($paymentCounts['pending'] ?? 0),
                'paid' => (int) ($paymentCounts['paid'] ?? 0),
                'refunded' => (int) ($paymentCounts['refunded'] ?? 0),
                'failed' => (int) ($paymentCounts['failed'] ?? 0),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function requestsByStatus(): array
    {
        $counts = $this->scopedServiceRequestQuery()
            ->select('service_requests.status', DB::raw('COUNT(*) as count'))
            ->groupBy('service_requests.status')
            ->pluck('count', 'status');

        return collect(ServiceRequestStatus::all())
            ->map(fn (string $status) => [
                'status' => $status,
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function requestsByOffice(): array
    {
        $rows = $this->scopedServiceRequestQuery()
            ->select('service_requests.office_id', DB::raw('COUNT(*) as request_count'))
            ->whereNotNull('service_requests.office_id')
            ->groupBy('service_requests.office_id')
            ->orderByDesc('request_count')
            ->get();

        return $this->attachOfficeMetadata($rows, 'request_count', 'count')->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function revenueByOffice(): array
    {
        $rows = $this->scopedPaidPaymentQuery()
            ->join('service_requests', 'service_requests.id', '=', 'payments.service_request_id')
            ->whereNotNull('service_requests.office_id')
            ->select(
                'service_requests.office_id',
                DB::raw('SUM(payments.amount) as revenue')
            )
            ->groupBy('service_requests.office_id')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => (object) [
                'office_id' => $row->office_id,
                'revenue' => (float) $row->revenue,
            ]);

        return $this->attachOfficeMetadata($rows, 'revenue', 'revenue', true)->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function requestsByDate(): array
    {
        return $this->scopedServiceRequestQuery()
            ->select(DB::raw('DATE(service_requests.created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(service_requests.created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function revenueByDate(): array
    {
        return $this->scopedPaidPaymentQuery()
            ->select(DB::raw('DATE(payments.paid_at) as date'), DB::raw('SUM(payments.amount) as amount'))
            ->whereNotNull('payments.paid_at')
            ->groupBy(DB::raw('DATE(payments.paid_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'amount' => round((float) $row->amount, 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function topServices(int $limit = 10): array
    {
        $rows = $this->scopedServiceRequestQuery()
            ->select('service_requests.service_id', DB::raw('COUNT(*) as request_count'))
            ->groupBy('service_requests.service_id')
            ->orderByDesc('request_count')
            ->limit($limit)
            ->get();

        $serviceNames = DB::table('services')
            ->whereIn('id', $rows->pluck('service_id'))
            ->pluck('name', 'id');

        return $rows
            ->map(fn ($row) => [
                'service_id' => (int) $row->service_id,
                'service_name' => $serviceNames[$row->service_id] ?? 'Unknown',
                'request_count' => (int) $row->request_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function byMunicipality(): array
    {
        $requestRows = $this->scopedServiceRequestQuery()
            ->join('offices', 'offices.id', '=', 'service_requests.office_id')
            ->select(
                'offices.municipality_id',
                DB::raw('COUNT(service_requests.id) as request_count')
            )
            ->groupBy('offices.municipality_id')
            ->get()
            ->keyBy('municipality_id');

        $revenueRows = $this->scopedPaidPaymentQuery()
            ->join('service_requests', 'service_requests.id', '=', 'payments.service_request_id')
            ->join('offices', 'offices.id', '=', 'service_requests.office_id')
            ->select(
                'offices.municipality_id',
                DB::raw('SUM(payments.amount) as revenue')
            )
            ->groupBy('offices.municipality_id')
            ->get()
            ->keyBy('municipality_id');

        $municipalityIds = $requestRows->keys()
            ->merge($revenueRows->keys())
            ->filter()
            ->unique()
            ->values();

        if ($municipalityIds->isEmpty()) {
            $unassignedRequests = (int) $this->scopedServiceRequestQuery()
                ->whereNull('office_id')
                ->count();

            if ($unassignedRequests === 0) {
                return [];
            }

            return [[
                'municipality_id' => null,
                'municipality_name' => 'Unassigned',
                'request_count' => $unassignedRequests,
                'revenue' => 0.0,
            ]];
        }

        $municipalities = DB::table('municipalities')
            ->whereIn('id', $municipalityIds)
            ->pluck('name', 'id');

        return $municipalityIds->map(function ($id) use ($requestRows, $revenueRows, $municipalities) {
            return [
                'municipality_id' => $id ? (int) $id : null,
                'municipality_name' => $id ? ($municipalities[$id] ?? 'Unknown') : 'Unassigned',
                'request_count' => (int) ($requestRows[$id]->request_count ?? 0),
                'revenue' => round((float) ($revenueRows[$id]->revenue ?? 0), 2),
            ];
        })->sortByDesc('request_count')->values()->all();
    }

    private function scopedServiceRequestQuery(): Builder
    {
        $query = ServiceRequest::query();

        if ($this->filters->officeId !== null) {
            $query->where('service_requests.office_id', $this->filters->officeId);
        }

        if ($this->filters->municipalityId !== null) {
            $query->whereHas('office', fn (Builder $officeQuery) => $officeQuery->where(
                'municipality_id',
                $this->filters->municipalityId
            ));
        }

        if ($this->filters->fromDate !== null) {
            $query->whereDate('service_requests.created_at', '>=', $this->filters->fromDate);
        }

        if ($this->filters->toDate !== null) {
            $query->whereDate('service_requests.created_at', '<=', $this->filters->toDate);
        }

        return $query;
    }

    private function scopedPaymentQuery(): Builder
    {
        $query = Payment::query()->whereHas('serviceRequest', function (Builder $requestQuery) {
            $this->applyOfficeScopeToRequestQuery($requestQuery);
        });

        if ($this->filters->fromDate !== null) {
            $query->whereDate('payments.created_at', '>=', $this->filters->fromDate);
        }

        if ($this->filters->toDate !== null) {
            $query->whereDate('payments.created_at', '<=', $this->filters->toDate);
        }

        return $query;
    }

    private function scopedPaidPaymentQuery(): Builder
    {
        $query = Payment::query()
            ->where('payments.status', 'paid')
            ->whereHas('serviceRequest', function (Builder $requestQuery) {
                $this->applyOfficeScopeToRequestQuery($requestQuery);
            });

        if ($this->filters->fromDate !== null) {
            $query->whereDate('payments.paid_at', '>=', $this->filters->fromDate);
        }

        if ($this->filters->toDate !== null) {
            $query->whereDate('payments.paid_at', '<=', $this->filters->toDate);
        }

        return $query;
    }

    private function applyOfficeScopeToRequestQuery(Builder $query): void
    {
        if ($this->filters->officeId !== null) {
            $query->where('office_id', $this->filters->officeId);
        }

        if ($this->filters->municipalityId !== null) {
            $query->whereHas('office', fn (Builder $officeQuery) => $officeQuery->where(
                'municipality_id',
                $this->filters->municipalityId
            ));
        }
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function attachOfficeMetadata(
        Collection $rows,
        string $sourceField,
        string $outputField,
        bool $roundMoney = false
    ): Collection {
        $officeIds = $rows->pluck('office_id')->filter()->unique()->values();

        $offices = Office::query()
            ->with('municipality:id,name')
            ->whereIn('id', $officeIds)
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($offices, $sourceField, $outputField, $roundMoney) {
            $office = $offices[$row->office_id] ?? null;
            $value = $row->{$sourceField};

            return [
                'office_id' => (int) $row->office_id,
                'office_name' => $office?->name ?? 'Unknown',
                'municipality_id' => $office?->municipality_id,
                'municipality_name' => $office?->municipality?->name,
                $outputField => $roundMoney ? round((float) $value, 2) : (int) $value,
            ];
        });
    }
}
