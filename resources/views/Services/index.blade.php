@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-2xl font-bold">
        Services
    </h1>

    <a href="{{ route('admin.services.create') }}">
        <x-button>
            + Add Service
        </x-button>
    </a>

</div>

<x-card>

    <x-table>

        <x-slot name="head">
            <th class="p-3">ID</th>
            <th class="p-3">Name</th>
            <th class="p-3">Actions</th>
        </x-slot>

        <x-slot name="body">

            <tr>
                <td class="p-3">1</td>
                <td class="p-3">Cleaning Service</td>
                <td class="p-3 flex gap-2">

                    <a href="{{ route('admin.services.edit', 1) }}">
                        <x-button color="secondary">Edit</x-button>
                    </a>

                    <x-button color="danger">
                        Delete
                    </x-button>

                </td>
            </tr>

        </x-slot>

    </x-table>

</x-card>

@endsection