<aside x-show="open"
       x-transition
       class="w-[240px] bg-white border-r border-gray-200 shadow-sm">

    <!-- LOGO -->
    <div class="h-[82px] flex items-center px-5">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
             class="w-12 h-12 object-contain">
    </div>

    <!-- MENU -->
    <nav>
        <!-- DASHBOARD -->
        <a href="{{ route('dewan.dashboard') }}"
           class="flex items-center gap-3 h-[60px] px-5 text-[#4fcfff] text-base font-medium hover:bg-gray-100 transition">
            <i class="fa-solid fa-user-tie w-5 text-center"></i>
            <span>Dewan</span>
        </a>

        <!-- DATA PETUGAS (ACTIVE) -->
        <a href="{{ route('dewan.petugas') }}"
           class="flex items-center gap-3 h-[60px] pl-12 pr-5 bg-[#dcf8ff] text-[#4fcfff] text-base font-medium">
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
