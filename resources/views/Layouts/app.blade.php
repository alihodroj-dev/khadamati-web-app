<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @include('components.sidebar')

        <div class="flex-1 flex flex-col">

            {{-- Navbar --}}
            @include('components.navbar')

            {{-- Main Content --}}
            <main class="p-6">
                {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-lg border border-green-200">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg border border-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="mb-4 px-4 py-3 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-200">
                            <p class="font-semibold mb-1">Please fix the following errors:</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                @yield('content')
                
            </main>

        </div>

    </div>

</body>
</html>