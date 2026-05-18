@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-500 mt-1">View and manage your account information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl p-6 shadow-sm text-center" style="border: 0.5px solid #e5e7eb;">
                <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ti ti-user text-blue-600 text-4xl"></i>
                </div>
                <h2 class="font-bold text-xl text-gray-900">{{ $user->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                <p class="text-xs text-gray-400 mt-2">Member since {{ $user->created_at->format('M d, Y') }}</p>
                
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('citizen.profile.edit') }}" 
                       class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Personal Information -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-user-circle text-blue-600"></i>
                    Personal Information
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-gray-500">Full Name</span>
                        <span class="font-medium text-gray-900">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-gray-500">Email Address</span>
                        <span class="font-medium text-gray-900">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-gray-500">Phone Number</span>
                        <span class="font-medium text-gray-900">{{ $user->phone ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-gray-500">National ID</span>
                        <span class="font-medium text-gray-900">{{ $user->national_id ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Account Status</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                    </div>
                </div>
            </div>
            
            <!-- Notification Preferences -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-bell text-blue-600"></i>
                    Notification Preferences
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900">Push Notifications</p>
                            <p class="text-xs text-gray-500">Receive updates on your browser</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $user->push_notifications_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $user->push_notifications_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900">Email Notifications</p>
                            <p class="text-xs text-gray-500">Receive updates via email</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $user->email_notifications_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $user->email_notifications_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900">SMS Notifications</p>
                            <p class="text-xs text-gray-500">Receive updates via text message</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $user->sms_notifications_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $user->sms_notifications_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Change Password Link -->
            <div class="bg-white rounded-xl p-6 shadow-sm" style="border: 0.5px solid #e5e7eb;">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-semibold text-gray-900">Security</h2>
                        <p class="text-sm text-gray-500">Update your password</p>
                    </div>
                    <button onclick="togglePasswordModal()" 
                            class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-50 transition">
                        Change Password
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Change Password</h2>
            <button onclick="togglePasswordModal()" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" action="{{ route('citizen.profile.password') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <input type="password" name="current_password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input type="password" name="password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" name="password_confirmation" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="togglePasswordModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePasswordModal() {
        const modal = document.getElementById('passwordModal');
        if (modal.style.display === 'none' || modal.style.display === '') {
            modal.style.display = 'flex';
        } else {
            modal.style.display = 'none';
        }
    }
</script>
@endsection