@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6 text-gray-900">Create Municipality</h1>

<x-card>
    <form method="POST" action="{{ route('admin.municipalities.store') }}">
        @csrf

        @include('admin.municipalities._form')

        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.municipalities.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                Back to Municipalities
            </a>
            <x-button type="submit">Create Municipality</x-button>
        </div>
    </form>
</x-card>

@endsection