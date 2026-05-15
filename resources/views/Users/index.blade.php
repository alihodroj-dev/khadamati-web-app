@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-2xl font-bold">
        Users
    </h1>

    <a href="{{ route('users.create') }}">
        <x-button>
            + Add User
        </x-button>
    </a>

</div>

<x-card>

    <x-table>

        <x-slot name="head">
            <th class="p-3">ID</th>
            <th class="p-3">Name</th>
            <th class="p-3">Email</th>
            <th class="p-3">Role</th>
            <th class="p-3">Status</th>
            <th class="p-3">Actions</th>
        </x-slot>

        <x-slot name="body">

            <tr>
                <td class="p-3">1</td>
                <td class="p-3">John Doe</td>
                <td class="p-3">john@example.com</td>

                <td class="p-3">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                        Admin
                    </span>
                </td>

                <td class="p-3">
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                        Active
                    </span>
                </td>

                <td class="p-3 flex gap-2">

                    <a href="{{ route('users.edit', 1) }}">
                        <x-button color="secondary">
                            Edit
                        </x-button>
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