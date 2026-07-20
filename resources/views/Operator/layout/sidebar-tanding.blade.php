<aside
    x-show="open"
    x-transition:enter="transition-transform duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="w-[238px] bg-white border-r border-gray-200 flex flex-col shrink-0 h-screen sticky top-0 overflow-y-auto overflow-x-hidden">

    <div class="h-[78px] flex items-center px-5 border-b border-gray-100">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
             alt="Logo IPSI"
             class="w-[52px] h-[52px] object-contain">
    </div>

    <nav class="flex-1 pt-1">

        <a href="{{ route('operator.tanding.index') }}"
           class="flex items-center gap-3 h-[60px] px-5 text-[18px] font-medium transition duration-200
           {{ request()->routeIs('operator.tanding.index') ? 'bg-[#dff7ff] text-[#57d2ff]' : 'text-[#57d2ff] hover:bg-gray-50' }}">
            <i data-lucide="layout-grid" class="w-5 h-5 stroke-[2.5]"></i>
            <span>Operator</span>
        </a>

        {{-- INPUT JADWAL — route: operator.tanding.add-jadwal --}}
        <a href="{{ route('operator.tanding.add-jadwal') }}"
           class="flex items-center gap-3 h-[60px] pl-14 pr-5 text-[18px] font-medium transition duration-200
           {{ request()->routeIs('operator.tanding.add-jadwal') ? 'bg-[#dff7ff] text-[#57d2ff]' : 'text-[#57d2ff] hover:bg-gray-50' }}">
            <i class="fa-solid fa-calendar-days text-[18px]"></i>
            <span>Input Jadwal</span>
        </a>

        {{-- INPUT PETUGAS — route: operator.tanding.add-petugas --}}
        <a href="{{ route('operator.tanding.add-petugas') }}"
           class="flex items-center gap-3 h-[60px] pl-14 pr-5 text-[18px] font-medium transition duration-200
           {{ request()->routeIs('operator.tanding.add-petugas') ? 'bg-[#dff7ff] text-[#57d2ff]' : 'text-[#57d2ff] hover:bg-gray-50' }}">
            <i class="fa-solid fa-users text-[18px]"></i>
            <span>Input Petugas</span>
        </a>

        <div class="px-5 mt-2 mb-3 text-[#a7e8ff] text-[18px] font-bold">
            Pages
        </div>

        {{-- MANAJEMEN AKUN --}}
        <a href="{{ route('operator.akun.index') }}"
            class="flex items-center gap-3 h-[45px] px-7 text-[#57d2ff] text-[18px] font-medium hover:bg-gray-50 transition duration-200">
            <i class="fa-solid fa-users-gear text-[18px]"></i>
            <span>Akun & Password</span>
        </a>

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 h-[45px] px-7 text-[#57d2ff] text-[18px] font-medium hover:bg-gray-50 transition duration-200">
            <i class="fa-solid fa-arrow-left text-[18px]"></i>
            <span>Kembali</span>
        </a>

    </nav>

</aside>