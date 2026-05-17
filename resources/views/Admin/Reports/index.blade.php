@extends('layouts.app')

@section('content')

@php
    $summary = $report['summary'];
    $paymentCounts = $summary['payment_counts'];
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
    <p class="text-sm text-gray-500">Revenue, requests, and performance across offices and municipalities</p>
</div>

<form method="GET" class="mb-6 bg-white p-4 rounded-xl shadow-sm border flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
        <input type="date" name="from_date" value="{{ $filters->fromDate }}"
               class="border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
        <input type="date" name="to_date" value="{{ $filters->toDate }}"
               class="border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Office</label>
        <select name="office_id" class="border rounded-lg px-3 py-2 text-sm min-w-[160px]">
            <option value="">All offices</option>
            @foreach($offices as $office)
                <option value="{{ $office->id }}" @selected($filters->officeId === $office->id)>
                    {{ $office->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Municipality</label>
        <select name="municipality_id" class="border rounded-lg px-3 py-2 text-sm min-w-[160px]">
            <option value="">All municipalities</option>
            @foreach($municipalities as $municipality)
                <option value="{{ $municipality->id }}" @selected($filters->municipalityId === $municipality->id)>
                    {{ $municipality->name }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Apply filters</button>
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline py-2">Reset</a>
</form>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-5 rounded-xl shadow-sm border">
        <p class="text-sm text-gray-500">Total Requests</p>
        <p class="text-2xl font-bold">{{ number_format($summary['total_requests']) }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border">
        <p class="text-sm text-gray-500">Total Revenue (paid)</p>
        <p class="text-2xl font-bold text-green-600">${{ number_format($summary['total_revenue'], 2) }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border md:col-span-2">
        <p class="text-sm text-gray-500 mb-2">Payment status</p>
        <div class="flex flex-wrap gap-4 text-sm">
            <span><strong class="text-yellow-600">{{ $paymentCounts['pending'] }}</strong> pending</span>
            <span><strong class="text-green-600">{{ $paymentCounts['paid'] }}</strong> paid</span>
            <span><strong class="text-gray-600">{{ $paymentCounts['refunded'] }}</strong> refunded</span>
            <span><strong class="text-red-600">{{ $paymentCounts['failed'] }}</strong> failed</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h2 class="text-lg font-bold mb-4">Requests per office</h2>
        @if(count($report['requests_by_office']) === 0)
            <p class="text-sm text-gray-400">No data for selected filters.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2">Office</th>
                            <th class="pb-2">Municipality</th>
                            <th class="pb-2 text-right">Requests</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['requests_by_office'] as $row)
                            <tr class="border-b border-gray-50">
                                <td class="py-2">{{ $row['office_name'] }}</td>
                                <td class="py-2 text-gray-500">{{ $row['municipality_name'] ?? '—' }}</td>
                                <td class="py-2 text-right font-medium">{{ $row['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h2 class="text-lg font-bold mb-4">Revenue per office (paid)</h2>
        @if(count($report['revenue_by_office']) === 0)
            <p class="text-sm text-gray-400">No paid revenue for selected filters.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2">Office</th>
                            <th class="pb-2">Municipality</th>
                            <th class="pb-2 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['revenue_by_office'] as $row)
                            <tr class="border-b border-gray-50">
                                <td class="py-2">{{ $row['office_name'] }}</td>
                                <td class="py-2 text-gray-500">{{ $row['municipality_name'] ?? '—' }}</td>
                                <td class="py-2 text-right font-medium text-green-600">${{ number_format($row['revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h2 class="text-lg font-bold mb-4">Requests by status</h2>
        <div class="space-y-2">
            @foreach($report['requests_by_status'] as $row)
                @if($row['count'] > 0)
                    <div class="flex justify-between text-sm py-1 border-b border-gray-50">
                        <span>{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</span>
                        <span class="font-medium">{{ $row['count'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h2 class="text-lg font-bold mb-4">Top services</h2>
        @if(count($report['top_services']) === 0)
            <p class="text-sm text-gray-400">No service activity.</p>
        @else
            <div class="space-y-2">
                @foreach($report['top_services'] as $row)
                    <div class="flex justify-between text-sm py-1 border-b border-gray-50">
                        <span>{{ $row['service_name'] }}</span>
                        <span class="font-medium">{{ $row['request_count'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@if(count($report['by_municipality']) > 0)
    <div class="bg-white p-6 rounded-xl shadow-sm border mb-8">
        <h2 class="text-lg font-bold mb-4">By municipality</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Municipality</th>
                        <th class="pb-2 text-right">Requests</th>
                        <th class="pb-2 text-right">Revenue (paid)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['by_municipality'] as $row)
                        <tr class="border-b border-gray-50">
                            <td class="py-2">{{ $row['municipality_name'] }}</td>
                            <td class="py-2 text-right">{{ $row['request_count'] }}</td>
                            <td class="py-2 text-right text-green-600">${{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
