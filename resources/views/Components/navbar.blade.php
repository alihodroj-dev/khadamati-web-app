<header class="bg-white shadow px-6 py-4 flex justify-between items-center">

    <h1 class="text-xl font-semibold">
        Dashboard
    </h1>

    <div class="flex items-center gap-4">

        <div class="text-gray-700">
            Admin User
        </div>

        <img
            src="https://ui-avatars.com/api/?name=Admin"
            class="w-10 h-10 rounded-full"
        >

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                style="background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer;"
            >
                Logout
            </button>
        </form>

    </div>

</header>