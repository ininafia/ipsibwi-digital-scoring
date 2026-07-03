<div class="bg-gray-200 rounded-lg p-4 mb-4 shadow border border-gray-300">
    <div class="grid grid-cols-[1fr_auto_1fr] gap-4">
        <!-- Header Kiri (Biru) -->
        <div class="grid grid-cols-3 bg-blue-700 text-white font-bold text-center py-2 shadow border border-blue-800">
            <div>Hukuman</div>
            <div>Binaan</div>
            <div>Jatuhan</div>
        </div>
        <!-- Header Tengah -->
        <div class="w-36 bg-black text-white font-bold text-center py-2 shadow">
            ROUND
        </div>
        <!-- Header Kanan (Merah) -->
        <div class="grid grid-cols-3 bg-red-600 text-white font-bold text-center py-2 shadow border border-red-700">
            <div>Jatuhan</div>
            <div>Binaan</div>
            <div>Hukuman</div>
        </div>
    </div>

    <!-- Baris Ronde -->
    @for ($i = 1; $i <= 3; $i++)
    <div class="grid grid-cols-[1fr_auto_1fr] gap-4 mt-2">
        <!-- Kolom Input Kiri -->
        <div class="grid grid-cols-3 gap-0 border border-gray-400">
            <div id="dewan-hukuman-biru-{{ $i }}" class="bg-gray-300 border-r border-gray-400 h-9 flex items-center justify-center font-bold text-red-600"></div>
            <div id="dewan-binaan-biru-{{ $i }}" class="bg-gray-300 border-r border-gray-400 h-9 flex items-center justify-center font-bold text-blue-700"></div>
            <div id="dewan-jatuhan-biru-{{ $i }}" class="bg-gray-300 h-9 flex items-center justify-center font-bold text-blue-700"></div>
        </div>
        <!-- Nomor Ronde -->
        <div id="dewan-round-indicator-{{ $i }}" class="w-36 bg-[#c5c6cc] text-white font-bold flex items-center justify-center shadow">
            {{ $i }}
        </div>
        <!-- Kolom Input Kanan -->
        <div class="grid grid-cols-3 gap-0 border border-gray-400">
            <div id="dewan-jatuhan-merah-{{ $i }}" class="bg-gray-300 border-r border-gray-400 h-9 flex items-center justify-center font-bold text-red-600"></div>
            <div id="dewan-binaan-merah-{{ $i }}" class="bg-gray-300 border-r border-gray-400 h-9 flex items-center justify-center font-bold text-red-600"></div>
            <div id="dewan-hukuman-merah-{{ $i }}" class="bg-gray-300 h-9 flex items-center justify-center font-bold text-blue-700"></div>
        </div>
    </div>
    @endfor
</div>
