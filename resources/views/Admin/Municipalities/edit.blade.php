@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">Edit Municipality #{{ $municipality->id }}</h1>

<x-card>
    <form method="POST" action="{{ route('admin.municipalities.update', $municipality->id) }}">
        @csrf
        @method('PUT')

        @include('admin.municipalities._form', ['municipality' => $municipality])

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('admin.municipalities.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                Back to Municipalities
            </a>
            <x-button type="submit">Update Municipality</x-button>
        </div>
    </form>
</x-card>

@endsection