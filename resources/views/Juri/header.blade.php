<header class="bg-gray-100 h-14 flex items-center justify-between px-4 shadow-sm">

    <!-- LOGO & TIMER & REC BADGE -->
    <div class="flex items-center gap-4">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}" class="w-[52px] h-[52px] object-contain" alt="Logo IPSI">
        <div id="timer-value" class="text-[20px] font-bold text-red-600 bg-white px-3 py-1 rounded shadow-inner border border-red-200">
            00:00
        </div>
        <div id="rec-status-badge" class="hidden items-center gap-1.5 px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full border border-red-300">
            <span class="w-2 h-2 bg-red-600 rounded-full animate-ping"></span>
            <span>REC LAYAR</span>
        </div>
        <button id="start-rec-btn" type="button" onclick="startManualRecording()" class="hidden items-center gap-1.5 px-3 py-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
            <i class="fa-solid fa-desktop text-[11px]"></i>
            <span>Aktifkan Rekam Layar</span>
        </button>
    </div>

    <!-- USER & LOGOUT -->
    <div class="flex items-center gap-3">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit"
               class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition">
                Logout
            </button>
        </form>
    </div>

</header>