@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Create Service
</h1>

<x-card>

    <form>

        <x-input label="Service Name" name="name" />

        <x-input label="Description" name="description" />

        <x-button type="submit">
            Save Service
        </x-button>

    </form>

</x-card>

@endsection