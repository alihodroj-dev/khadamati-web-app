@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">Create Office</h1>

<x-card>
    <form method="POST" action="{{ route('admin.offices.store') }}">
        @csrf

        @include('admin.offices._form', ['municipalities' => $municipalities])

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('admin.offices.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                Back to Offices
            </a>
            <x-button type="submit">Save Office</x-button>
        </div>
    </form>
</x-card>

@endsection
