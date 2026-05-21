@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.municipalities.index') }}" class="hover:text-blue-600 transition">Municipalities</a>
            <span>/</span>
            <span>Details</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            Municipality Details <span class="text-gray-400 font-mono text-lg ml-2">{{ $municipality->id }}</span>
        </h1>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.municipalities.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
            Back
        </a>
        <a href="{{ route('admin.municipalities.edit', $municipality->id) }}">
            <x-button color="secondary">
                Edit Municipality
            </x-button>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- Main Information --}}
    <div class="lg:col-span-2 space-y-6">
        <x-card>
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-lg font-bold text-gray-800">Primary Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Municipality Name</h3>
                    <p class="text-xl font-bold text-gray-900">{{ $municipality->name }}</p>
                </div>

                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Municipality Code</h3>
                    <p class="text-lg text-gray-700">
                        <code class="bg-gray-100 px-2 py-1 rounded">{{ $municipality->code }}</code>
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email</h3>
                    <p class="text-gray-700">
                        @if($municipality->email)
                            <a href="mailto:{{ $municipality->email }}" class="text-blue-600 hover:underline">
                                {{ $municipality->email }}
                            </a>
                        @else
                            <span class="text-gray-400">Not provided</span>
                        @endif
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Phone</h3>
                    <p class="text-gray-700">{{ $municipality->phone ?? 'Not provided' }}</p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Address</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $municipality->address ?? 'No address provided' }}</p>
                </div>
            </div>
        </x-card>

        {{-- Offices Section --}}
        <x-card>
            <div class="border-b border-gray-100 pb-4 mb-6 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Offices under {{ $municipality->name }}</h2>
                <a href="{{ route('admin.offices.create') }}?municipality_id={{ $municipality->id }}" 
                   class="text-sm text-blue-600 hover:text-blue-800">
                    + Add Office
                </a>
            </div>

            @if($municipality->offices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200">
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="pb-3">ID</th>
                                <th class="pb-3">Name</th>
                                <th class="pb-3">Phone</th>
                                <th class="pb-3">Requests</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($municipality->offices as $office)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 text-sm">{{ $office->id }}</td>
                                    <td class="py-3 text-sm font-medium">{{ $office->name }}</td>
                                    <td class="py-3 text-sm">{{ $office->phone ?? '—' }}</td>
                                    <td class="py-3 text-sm">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
                                            {{ $office->service_requests_count ?? 0 }} requests
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm">
                                        <span style="{{ $office->is_active ? 'background-color:#dcfce7;color:#166534' : 'background-color:#fee2e2;color:#991b1b' }}; padding:2px 8px; border-radius:9999px; font-size:11px; font-weight:500;">
                                            {{ $office->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm">
                                        <a href="{{ route('admin.offices.show', $office->id) }}" class="text-blue-600 hover:underline text-xs mr-2">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="ti ti-building-community text-4xl mb-2 block"></i>
                    <p>No offices assigned to this municipality yet.</p>
                    <a href="{{ route('admin.offices.create') }}?municipality_id={{ $municipality->id }}" 
                       class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                        Create the first office →
                    </a>
                </div>
            @endif
        </x-card>
    </div>

    {{-- Sidebar Details --}}
    <div class="space-y-6">
        <x-card>
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-lg font-bold text-gray-800">Statistics</h2>
            </div>

            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 font-medium">Total Offices</span>
                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg font-semibold">
                        {{ $municipality->offices->count() }}
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500 font-medium">Active Offices</span>
                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-lg font-semibold">
                        {{ $municipality->offices->where('is_active', true)->count() }}
                    </span>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Status</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $municipality->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }} ring-1 ring-inset {{ $municipality->is_active ? 'ring-emerald-600/10' : 'ring-gray-400/20' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $municipality->is_active ? 'bg-emerald-600' : 'bg-gray-400' }}"></span>
                            {{ $municipality->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-lg font-bold text-gray-800">Quick Actions</h2>
            </div>

            <div class="space-y-3">
                <a href="{{ route('admin.offices.create') }}?municipality_id={{ $municipality->id }}" 
                   class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    + Add Office to this Municipality
                </a>
                
                <a href="{{ route('admin.users.create') }}?municipality_id={{ $municipality->id }}" 
                   class="block w-full text-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                    + Add Municipality Admin
                </a>
            </div>
        </x-card>
    </div>
</div>

@endsection