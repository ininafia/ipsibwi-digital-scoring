<header class="bg-gray-100 h-14 flex items-center justify-between px-4 shadow-sm">

    <!-- LOGO -->
    <div class="flex items-center gap-4">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}" class="w-9 h-9 object-contain" alt="Logo IPSI">
        <div id="timer-value" class="text-[20px] font-bold text-red-600 bg-white px-3 py-1 rounded shadow-inner border border-red-200">
            00:00
        </div>
    </div>

    <!-- USER & LOGOUT -->
    <div class="flex items-center gap-4">

        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit"
               class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition">
                Logout
            </button>
        </form>

    </div>

</header>