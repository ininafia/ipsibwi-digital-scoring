<aside
    x-show="open"
    x-transition
    class="w-[238px] bg-white border-r border-gray-200 flex flex-col shrink-0">

    {{-- LOGO --}}
    <div class="h-[78px] flex items-center px-5 border-b border-gray-100">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
             alt="Logo IPSI"
             class="w-[52px] h-[52px] object-contain">
    </div>

    <nav class="flex-1 pt-1">

        {{-- DASHBOARD PETUGAS --}}
        <a href="{{ route('operator.petugas.index') }}"
           class="flex items-center gap-3 h-[60px] px-5 text-[18px] font-medium transition duration-200
           {{ request()->routeIs('operator.petugas.index') ? 'bg-[#dff7ff] text-[#57d2ff]' : 'text-[#57d2ff] hover:bg-gray-50' }}">

            <i data-lucide="users" class="w-5 h-5 stroke-[2.5]"></i>

            <span>Petugas</span>

        </a>

        {{-- INPUT PETUGAS --}}
        <a href="{{ route('operator.petugas.add') }}"
           class="flex items-center gap-3 h-[60px] pl-14 pr-5 text-[18px] font-medium transition duration-200
           {{ request()->routeIs('operator.petugas.add') ? 'bg-[#dff7ff] text-[#57d2ff]' : 'text-[#57d2ff] hover:bg-gray-50' }}">

            <i class="fa-solid fa-user-plus text-[18px]"></i>

            <span>Input Petugas</span>

        </a>

        {{-- DATA PETUGAS --}}
        <a href="{{ route('operator.petugas.data') }}"
           class="flex items-center gap-3 h-[60px] pl-14 pr-5 text-[18px] font-medium transition duration-200
           {{ request()->routeIs('operator.petugas.data') ? 'bg-[#dff7ff] text-[#57d2ff]' : 'text-[#57d2ff] hover:bg-gray-50' }}">

            <i class="fa-solid fa-address-card text-[18px]"></i>

            <span>Data Petugas</span>

        </a>

        {{-- TITLE --}}
        <div class="px-5 mt-2 mb-3 text-[#a7e8ff] text-[18px] font-bold">
            Pages
        </div>

        {{-- KEMBALI --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 h-[45px] px-7 text-[#57d2ff] text-[18px] font-medium hover:bg-gray-50 transition duration-200">

            <i class="fa-solid fa-arrow-left text-[18px]"></i>

            <span>Kembali</span>

        </a>

    </nav>

</aside>