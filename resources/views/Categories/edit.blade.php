@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit Category #{{ $id }}
</h1>

<x-card>

    <form>

        <x-input
            label="Category Name"
            name="name"
            value="Example Category"
        />

        <x-button type="submit">
            Update Category
        </x-button>

    </form>

</x-card>

@endsection