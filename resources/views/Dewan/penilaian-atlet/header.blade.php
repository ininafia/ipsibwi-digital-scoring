<header class="bg-gray-100 h-14 flex items-center justify-between px-4 shadow-sm">
    <!-- LOGO -->
    <div class="flex items-center gap-4">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}" class="w-[52px] h-[52px] object-contain" alt="Logo IPSI">
        <div id="timer-value" class="text-[20px] font-bold text-red-600 bg-white px-3 py-1 rounded shadow-inner border border-red-200">
            00:00
        </div>
    </div>

    <!-- KEMBALI -->
    <div class="flex items-center gap-4">
        <a href="{{ route('dewan.petugas') }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded-md text-sm font-medium flex items-center gap-2 transition duration-200">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>
</header>
