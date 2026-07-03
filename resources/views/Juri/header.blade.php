<header class="bg-gray-100 h-14 flex items-center justify-between px-4 shadow-sm">

    <!-- LOGO -->
    <img
        src="{{ asset('images/logos/LOGO IPSI.png') }}"
        class="w-9 h-9 object-contain"
        alt="Logo IPSI">

    <!-- USER & LOGOUT -->
    <div class="flex items-center gap-4">

        <a href="{{ url('/logout') }}"
           class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition">
            Logout
        </a>

    </div>

</header>