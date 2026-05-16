@extends('layouts.app')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
    <p class="text-sm text-gray-500 mt-1">Welcome back, Admin. Here is what's happening today.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Users</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">120</p>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500 font-medium">+12%</span>
            <span class="text-gray-400 ml-2">since last month</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Active Services</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">35</p>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-400">
            <span>5 pending approval</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">New Requests</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">410</p>
            </div>
            <div class="p-3 bg-amber-50 rounded-lg">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-red-500 font-medium">24 High Priority</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Revenue</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">$12,400</p>
            </div>
            <div class="p-3 bg-emerald-50 rounded-lg">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-gray-400">
            <span>Last 30 days</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <h3 class="text-lg font-bold text-gray-900">Quick Actions</h3>
        
        <a href="{{ route('admin.services.create') }}" class="flex items-center p-4 bg-white rounded-xl border border-gray-200 shadow-sm hover:border-blue-500 hover:shadow-md transition group">
            <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <span class="ml-4 font-medium text-gray-700">Add New Service</span>
        </a>

        <a href="{{ route('admin.users.create') }}" class="flex items-center p-4 bg-white rounded-xl border border-gray-200 shadow-sm hover:border-blue-500 hover:shadow-md transition group">
            <div class="p-2 bg-gray-100 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <span class="ml-4 font-medium text-gray-700">Create Staff Account</span>
        </a>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm h-full">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-900">System Activity</h3>
                <button class="text-sm text-blue-600 font-medium">View All</button>
            </div>
            <div class="p-6">
                <p class="text-gray-400 text-sm italic">New requests and user sign-ups will appear here...</p>
            </div>
        </div>
    </div>
</div>

@endsection