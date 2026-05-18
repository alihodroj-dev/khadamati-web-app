@extends('layouts.app')

@section('title', 'Browse Services')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Government Services</h1>
        <p class="text-gray-500 mt-1">Browse and request available government services</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Sidebar Filters -->
        <div class="lg:w-72 flex-shrink-0">
            <div class="bg-white rounded-xl p-5 shadow-sm sticky top-6" style="border: 0.5px solid #e5e7eb;">
                
                <form method="GET" action="{{ route('citizen.services.index') }}" id="filterForm">
                    <!-- Search -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <i class="ti ti-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search services..." 
                                   class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Office Filter -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Office</label>
                        <select name="office_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All Offices</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                    {{ $office->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reset Filters Button -->
                    @if(request()->anyFilled(['search', 'category_id', 'office_id']))
                        <a href="{{ route('citizen.services.index') }}" class="text-sm text-blue-600 hover:underline block text-center mt-4">
                            Clear all filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="flex-1">
            
            <!-- Results Count -->
            <div class="mb-4 text-sm text-gray-500">
                Found {{ $services->total() }} service{{ $services->total() != 1 ? 's' : '' }}
            </div>

            @if($services->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($services as $service)
                        <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition-all" style="border: 0.5px solid #e5e7eb;">
                            
                            <!-- Service Icon & Category -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="ti ti-briefcase text-blue-600 text-xl"></i>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                    {{ $service->category->name ?? 'General' }}
                                </span>
                            </div>
                            
                            <!-- Service Name -->
                            <h3 class="font-semibold text-gray-900 mb-1 text-lg">{{ $service->name }}</h3>
                            
                            <!-- Description -->
                            <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ Str::limit($service->description, 100) }}</p>
                            
                            <!-- Office & Fee -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                    <i class="ti ti-building"></i>
                                    <span>{{ $service->office->name ?? 'Any Office' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold text-blue-900">${{ number_format($service->base_fee, 2) }}</span>
                                    @if($service->estimated_processing_days)
                                        <p class="text-xs text-gray-400">{{ $service->estimated_processing_days }} days</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <a href="{{ route('citizen.services.show', $service->id) }}" 
                                   class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                    View Details
                                </a>
                                <a href="{{ route('citizen.services.request.create', $service->id) }}" 
                                   class="flex-1 text-center px-3 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                                    Request Now
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $services->withQueryString()->links() }}
                </div>
                
            @else
                <div class="bg-white rounded-xl p-12 text-center" style="border: 0.5px solid #e5e7eb;">
                    <i class="ti ti-search text-gray-300 text-5xl mb-3 block"></i>
                    <p class="text-gray-500 mb-2">No services found</p>
                    <p class="text-sm text-gray-400">Try adjusting your filters or search term</p>
                    <a href="{{ route('citizen.services.index') }}" class="inline-block mt-4 text-blue-600 hover:underline">
                        Clear all filters
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection