<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Khadamati Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">

        {{-- Logo / Title --}}
        <div class="text-center mb-8">

            <h1 class="text-4xl font-bold text-blue-900">
                Khadamati
            </h1>

            <p class="text-gray-600 mt-2">
                Government Services Dashboard
            </p>

        </div>

        {{-- Login Card --}}
        <div class="bg-white shadow-xl rounded-2xl p-8">

            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                Sign In
            </h2>

            {{-- Validation Errors --}}
            @if ($errors->any())

                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4">

                    <ul class="list-disc list-inside text-sm">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ url('/login') }}">

                @csrf

                {{-- Email --}}
                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter your email"
                    >

                </div>

                {{-- Password --}}
                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter your password"
                    >

                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between mb-6">

                    <label class="flex items-center">

                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        >

                        <span class="ml-2 text-sm text-gray-600">
                            Remember me
                        </span>

                    </label>

                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-xl transition duration-200"
                >
                    Login
                </button>

            </form>

        </div>

        {{-- Footer --}}
        <p class="text-center text-sm text-gray-500 mt-6">
            © {{ date('Y') }} Khadamati. All rights reserved.
        </p>

    </div>

</body>
</html>