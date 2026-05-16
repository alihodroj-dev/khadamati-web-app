@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.services.index') }}" class="hover:text-blue-600 transition">Services</a>
            <span>/</span>
            <span>Details</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            Service Details <span class="text-gray-400 font-mono text-lg ml-2">#{{ $id }}</span>
        </h1>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
            Back
        </a>
        <a href="{{ route('admin.services.edit', $id) }}">
            <x-button color="secondary">
                Edit Service
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
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Service Name</h3>
                    <p class="text-xl font-bold text-gray-900">Passport Renewal</p>
                </div>

                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Category</h3>
                    <p class="text-lg text-gray-700">Civil Services</p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Description</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Renew passport service description. This covers the official renewal process for expired or soon-to-expire documents.
                    </p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Required Documents</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-sm border border-gray-200">ID Copy</span>
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-sm border border-gray-200">Personal Photos (2x)</span>
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-sm border border-gray-200">Old Passport</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Sidebar Details --}}
    <div class="space-y-6">
        <x-card>
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-lg font-bold text-gray-800">Operational Stats</h2>
            </div>

            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 font-medium">Base Fee</span>
                    <span class="text-xl font-bold text-gray-900">$20.00</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500 font-medium">Processing Time</span>
                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg font-semibold">5 Business Days</span>
                </div>

                <div class="pt-4 border-t border-gray-100 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Appointment</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                            Required
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Status</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Active
                        </span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>

@endsection