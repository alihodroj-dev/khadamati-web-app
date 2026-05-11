<header class="bg-white shadow px-6 py-4 flex justify-between items-center">

    <h1 class="text-xl font-semibold">
        Dashboard
    </h1>

    <div class="flex items-center gap-4">

    <form method="POST" action="{{ route('logout') }}">
    @csrf

    <button
            type="submit"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg"
        >
            Logout
        </button>
    </form>

        <span class="text-gray-700">
            Admin User
        </span>

        <img
            src="https://ui-avatars.com/api/?name=Admin"
            class="w-10 h-10 rounded-full"
        >

    </div>

</header>