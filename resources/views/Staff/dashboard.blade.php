@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Staff Dashboard
</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Assigned Requests</h2>
        <p class="text-3xl font-bold mt-2">{{ $assignedRequests }}</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Appointments</h2>
        <p class="text-3xl font-bold mt-2">{{ $appointments }}</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Completed Tasks</h2>
        <p class="text-3xl font-bold mt-2">{{ $completedTasks }}</p>
    </div>

</div>

@endsection