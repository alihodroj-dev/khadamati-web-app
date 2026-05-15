@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Create Category
</h1>

<x-card>

    <form>

        <x-input
            label="Category Name"
            name="name"
        />

        <x-button type="submit">
            Save Category
        </x-button>

    </form>

</x-card>

@endsection