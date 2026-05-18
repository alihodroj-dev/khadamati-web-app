@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md" style="border: 0.5px solid #e5e7eb;">
        
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Verify Your Identity</h1>
            <p class="text-gray-500 mt-2">Enter the 6-digit code sent to your email</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('citizen.auth.otp.verify') }}">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                <input type="text" name="otp" required maxlength="6" autocomplete="off"
                    class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="000000">
                @error('otp')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-900 hover:bg-blue-800 text-white font-medium py-2 px-4 rounded-lg transition">
                Verify & Sign In
            </button>
        </form>

        <div class="text-center mt-6">
            <form method="POST" action="{{ route('citizen.auth.otp.resend') }}">
                @csrf
                <button type="submit" class="text-sm text-blue-600 hover:underline">
                    Didn't receive the code? Resend
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-600 mt-6">
            <a href="{{ route('citizen.auth.login') }}" class="text-blue-600 hover:underline">
                ← Back to login
            </a>
        </p>
    </div>
</div>
@endsection