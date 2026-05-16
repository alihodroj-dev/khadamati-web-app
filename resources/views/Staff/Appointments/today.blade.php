@extends('layouts.app')

@section('content')

<div class="mb-6 flex justify-between items-center">

    <div>
        <h1 class="text-2xl font-bold">
            Today's Schedule
        </h1>
        <p class="text-sm text-gray-500">
            {{ now()->format('l, F j, Y') }}
        </p>
    </div>

    <a href="{{ route('staff.appointments.index') }}"
       class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
        All Appointments
    </a>

</div>

@if($appointments->isEmpty())

    <div class="bg-white p-12 rounded-xl shadow text-center">
        <p class="text-gray-400 text-lg">No appointments scheduled for today</p>
        <p class="text-gray-300 text-sm mt-1">Enjoy your free day!</p>
    </div>

@else

    <div class="space-y-4">

        @foreach($appointments as $app)

            <div class="bg-white p-6 rounded-xl shadow-sm border flex items-center justify-between gap-6">

                {{-- TIME --}}
                <div class="text-center min-w-[80px]">
                    <p class="text-2xl font-bold text-blue-600">
                        {{ \Carbon\Carbon::parse($app->appointment_time)->format('h:i') }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($app->appointment_time)->format('A') }}
                    </p>
                </div>

                {{-- DIVIDER --}}
                <div class="w-px bg-gray-200 self-stretch"></div>

                {{-- INFO --}}
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">
                        {{ $app->user->name ?? '-' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $app->serviceRequest->service->name ?? '-' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Ref: {{ $app->serviceRequest->reference_number ?? '-' }}
                    </p>
                </div>

                {{-- STATUS --}}
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($app->status === 'scheduled') bg-blue-100 text-blue-700
                        @elseif($app->status === 'in_progress') bg-indigo-100 text-indigo-700
                        @elseif($app->status === 'completed') bg-green-100 text-green-700
                        @elseif($app->status === 'cancelled') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-600
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                    </span>
                </div>

                {{-- ACTION --}}
                <div>
                    <a href="{{ route('staff.appointments.show', $app->id) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                        Manage
                    </a>
                </div>

            </div>

        @endforeach

    </div>

@endif

@endsection