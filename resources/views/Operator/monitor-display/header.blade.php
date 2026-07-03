<div class="grid grid-cols-3 items-center mb-2 lg:mb-4">
    <div class="flex items-center gap-2 justify-center">
        <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full border-[2px] border-blue-600 flex items-center justify-center bg-gray-200 overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}" class="w-full h-full object-contain p-1">
        </div>
        <div>
            <h2 id="monitor-nama-biru" class="text-blue-600 font-bold text-[10px] lg:text-xs">{{ $match->sudut_biru ?? 'Nama Atlet' }}</h2>
            <p id="monitor-sekolah-biru" class="font-semibold text-[9px] lg:text-[10px] text-black">{{ $match->kontingen_biru ?? 'Asal Kontingen' }}</p>
        </div>
    </div>

    <div class="flex flex-col items-center justify-center">
        <div id="monitor-timer" class="text-center text-2xl lg:text-4xl font-sans font-bold">00 : 00</div>
    </div>

    <div class="flex items-center gap-2 justify-center">
        <div class="text-right">
            <h2 id="monitor-nama-merah" class="text-red-600 font-bold text-[10px] lg:text-xs">{{ $match->sudut_merah ?? 'Nama Atlet' }}</h2>
            <p id="monitor-sekolah-merah" class="font-semibold text-[9px] lg:text-[10px] text-black">{{ $match->kontingen_merah ?? 'Asal Kontingen' }}</p>
        </div>
        <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full border-[2px] border-red-600 flex items-center justify-center bg-gray-200 overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}" class="w-full h-full object-contain p-1" style="transform: scaleX(-1);">
        </div>
    </div>
</div>
