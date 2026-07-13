<aside
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="absolute z-40 md:relative h-full w-[238px] bg-white border-r border-gray-200 flex flex-col shrink-0">

    <!-- LOGO -->
    <div class="h-[82px] flex items-center px-5">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
             class="w-12 h-12 object-contain">
    </div>

    <!-- MENU -->
    <nav>
        <!-- DASHBOARD (ACTIVE) -->
        <a href="{{ route('dewan.dashboard') }}"
           class="flex items-center gap-3 h-[60px] px-5 bg-[#dcf8ff] text-[#4fcfff] text-base font-medium">
            <i class="fa-solid fa-user-tie w-5 text-center"></i>
            <span>Dewan</span>
        </a>

        <!-- DATA PETUGAS -->
        <a href="{{ route('dewan.petugas') }}"
           class="flex items-center gap-3 h-[60px] pl-12 pr-5 text-[#4fcfff] text-base font-medium hover:bg-gray-100 transition">
            <i class="fa-solid fa-user-group w-5 text-center"></i>
            <span>Data Petugas</span>
        </a>

        <!-- PENILAIAN -->
        <a href="{{ route('dewan.penilaian') }}"
           class="flex items-center gap-3 h-[60px] pl-12 pr-5 text-[#4fcfff] text-base font-medium hover:bg-gray-100 transition">
            <i class="fa-solid fa-people-roof w-5 text-center"></i>
            <span>Penilaian Atlet</span>
        </a>

        <!-- TITLE -->
        <div class="px-5 mt-1 mb-3 text-[#9fe6ff] text-base font-bold">
            Pages
        </div>

        <!-- LOGOUT -->
        <a href="{{ route('logout') }}"
           class="flex items-center gap-3 h-[45px] px-7 text-[#4fcfff] text-base font-medium hover:bg-gray-100 transition">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>
