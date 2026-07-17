<header class="bg-gray-100 h-24 shadow-sm flex items-center justify-between px-6">

    <!-- LOGO -->
    <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
         class="w-12 h-12 object-contain"
         alt="logo">

    <!-- USER -->
    <div class="flex items-center gap-4">

        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit"
               class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">

                <i class="fa-solid fa-right-from-bracket"></i>
                Logout

            </button>
        </form>

    </div>

</header>