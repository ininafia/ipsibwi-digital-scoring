<aside
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="absolute z-40 md:relative h-full w-[238px] bg-white border-r border-gray-200 flex flex-col shrink-0">

    {{-- LOGO --}}
    <div class="h-[78px] flex items-center px-5 border-b border-gray-100">

        <img
            src="{{ asset('images/logos/LOGO IPSI.png') }}"
            alt="Logo IPSI"
            class="w-[52px] h-[52px] object-contain">

    </div>

    {{-- MENU --}}
    <nav class="flex-1 pt-1">

        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 h-[60px] px-5 bg-[#dff7ff] text-[#57d2ff] text-[18px] font-medium">

            <i data-lucide="layout-grid"
                class="w-5 h-5 stroke-[2.5]"></i>

            <span>Dashboard</span>

        </a>

        {{-- OPERATOR --}}
        <a href="{{ route('operator.tanding.index') }}"
            class="flex items-center gap-3 h-[60px] pl-14 pr-5 text-[#57d2ff] text-[18px] font-medium hover:bg-gray-50 transition duration-200">

            <i class="fa-solid fa-desktop text-[18px]"></i>

            <span>Operator</span>

        </a>

         {{-- MONITOR DISPLAY --}}
<a href="{{ route('operator.monitor-display') }}"
    class="flex items-center gap-3 h-[60px] pl-14 pr-5 text-[#57d2ff] text-[18px] font-medium hover:bg-gray-50 transition duration-200">

    <i class="fa-solid fa-video text-[18px]"></i>

    <span>Monitor</span>

</a>

        {{-- TITLE --}}
        <div class="px-5 mt-2 mb-3 text-[#a7e8ff] text-[18px] font-bold">

            Pages

        </div>

        {{-- LOGOUT --}}
        <a href="{{ route('logout') }}"
            class="flex items-center gap-3 h-[45px] px-7 text-[#57d2ff] text-[18px] font-medium hover:bg-gray-50 transition duration-200">

            <i class="fa-solid fa-right-from-bracket text-[18px]"></i>

            <span>Logout</span>

        </a>

    </nav>

</aside>