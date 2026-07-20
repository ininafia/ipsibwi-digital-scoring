<aside x-show="open"
    x-transition:enter="transition-transform duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="w-[240px] bg-white border-r border-gray-200 shadow-sm shrink-0 h-screen sticky top-0 overflow-y-auto overflow-x-hidden">

    <!-- LOGO -->
    <div class="h-[82px] flex items-center px-5">

        <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
            class="w-12 h-12 object-contain">

    </div>

    <!-- MENU -->
    <nav>

        <!-- DASHBOARD -->
        <a href="{{ route('ketua.dashboard') }}"
            class="flex items-center gap-3 h-[60px] px-5 bg-[#dcf8ff] text-[#4fcfff] text-base font-medium whitespace-nowrap">

            <i class="fa-solid fa-user-tie w-5 text-center"></i>
            <span>Ketua Pertandingan</span>

        </a>

        <!-- MONITOR PERTANDINGAN -->
        <a href="{{ route('ketua.monitor') }}"
            class="flex items-center gap-3 h-[60px] pl-12 pr-5 {{ request()->routeIs('ketua.monitor') ? 'bg-[#dcf8ff] text-[#4fcfff]' : 'text-[#4fcfff] hover:bg-gray-100' }} text-base font-medium transition">

            <i class="fa-solid fa-people-roof w-5 text-center"></i>
            <span>Monitor</span>

        </a>

        <!-- LOG JURI -->
        <a href="{{ route('ketua.log-juri') }}"
            class="flex items-center gap-3 h-[60px] pl-12 pr-5 {{ request()->routeIs('ketua.log-juri') ? 'bg-[#dcf8ff] text-[#4fcfff]' : 'text-[#4fcfff] hover:bg-gray-100' }} text-base font-medium transition">

            <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
            <span>Log Activity Juri</span>

        </a>

        <!-- PERSENTASE JURI -->
        <a href="{{ route('ketua.akurasi') }}"
            class="flex items-center gap-3 h-[60px] pl-12 pr-5 {{ request()->routeIs('ketua.akurasi') ? 'bg-[#dcf8ff] text-[#4fcfff]' : 'text-[#4fcfff] hover:bg-gray-100' }} text-base font-medium transition">

            <i class="fa-solid fa-user-group w-5 text-center"></i>
            <span>Persentase Juri</span>

        </a>

        <!-- TITLE -->
        <div class="px-5 mt-1 mb-3 text-[#9fe6ff] text-base font-bold">
            Pages
        </div>

        <!-- LOGOUT -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-3 h-[45px] px-7 text-[#4fcfff] text-base font-medium hover:bg-gray-100 transition w-full text-left bg-transparent border-0 cursor-pointer">

                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>

            </button>
        </form>

    </nav>

</aside>
