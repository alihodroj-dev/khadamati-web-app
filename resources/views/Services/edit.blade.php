@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Edit Service #{{ $id }}
</h1>

<x-card>

    <form>

        <x-input label="Service Name" name="name" value="Example Service" />

        <x-input label="Description" name="description" value="Example description" />

        <x-button type="submit">
            Update Service
        </x-button>

    </form>

</x-card>

@endsection