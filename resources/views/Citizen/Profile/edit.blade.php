@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.profile.show') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Profile</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Edit Profile</h1>
            <p class="text-blue-100 text-sm mt-1">Update your personal information</p>
        </div>
        
        <div class="p-6">
            
            <form method="POST" action="{{ route('citizen.profile.update') }}">
                @csrf
                @method('PUT')
                
                <!-- Name -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email (read-only) -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">Email cannot be changed</p>
                </div>
                
                <!-- Phone -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- National ID (read-only) -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">National ID</label>
                    <input type="text" value="{{ $user->national_id ?? 'Not provided' }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                </div>
                
                <!-- Notification Preferences -->
                <div class="mb-5 pt-4 border-t border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-3">Notification Preferences</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-700">Push Notifications</span>
                            <input type="checkbox" name="push_notifications_enabled" value="1" 
                                   {{ $user->push_notifications_enabled ? 'checked' : '' }}
                                   class="toggle-switch">
                        </label>
                        
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-700">Email Notifications</span>
                            <input type="checkbox" name="email_notifications_enabled" value="1" 
                                   {{ $user->email_notifications_enabled ? 'checked' : '' }}
                                   class="toggle-switch">
                        </label>
                        
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-700">SMS Notifications</span>
                            <input type="checkbox" name="sms_notifications_enabled" value="1" 
                                   {{ $user->sms_notifications_enabled ? 'checked' : '' }}
                                   class="toggle-switch">
                        </label>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <a href="{{ route('citizen.profile.show') }}" 
                       class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .toggle-switch {
        width: 44px;
        height: 22px;
        background-color: #ccc;
        border-radius: 999px;
        appearance: none;
        cursor: pointer;
        position: relative;
        transition: background-color 0.2s;
    }
    .toggle-switch:checked {
        background-color: #2563eb;
    }
    .toggle-switch::before {
        content: '';
        width: 18px;
        height: 18px;
        background-color: white;
        border-radius: 50%;
        position: absolute;
        top: 2px;
        left: 3px;
        transition: transform 0.2s;
    }
    .toggle-switch:checked::before {
        transform: translateX(22px);
    }
</style>
@endsection