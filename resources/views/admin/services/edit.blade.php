@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">Edit Service #{{ $service->id }}</h1>

<x-card>
    <form method="POST" action="{{ route('admin.services.update', $service->id) }}">
        @csrf
        @method('PUT')

        @include('admin.services._form', ['service' => $service, 'categories' => $categories, 'offices' => $offices])

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                Back to Services
            </a>
            <x-button type="submit">Update Service</x-button>
        </div>
    </form>
</x-card>

@endsection
