@extends('layouts.app')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Users</h2>
        <p class="text-3xl font-bold mt-2">120</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Services</h2>
        <p class="text-3xl font-bold mt-2">35</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Requests</h2>
        <p class="text-3xl font-bold mt-2">410</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-gray-500">Revenue</h2>
        <p class="text-3xl font-bold mt-2">$12,400</p>
    </div>

</div>

@endsection