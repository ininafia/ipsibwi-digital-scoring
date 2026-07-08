<div class="flex justify-between items-center mb-4 px-2">
    <!-- Sudut Biru (Kiri) -->
    <div class="flex items-center gap-4">
        <div class="w-12 h-7 border overflow-hidden shadow rounded-md">
            <div class="h-1/2 bg-red-600"></div>
            <div class="h-1/2 bg-white"></div>
        </div>
        <div class="w-9 h-9 rounded-full border-2 border-blue-700 bg-white flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}" class="w-5 h-5 object-contain">
        </div>
        <div>
            <p class="text-sm font-bold text-blue-800" id="peserta-nama-biru">-</p>
            <p class="text-xs text-gray-500" id="peserta-kontingen-biru">-</p>
        </div>
    </div>

    <!-- Info Partai -->
    <div class="text-center">
        <p class="text-lg font-extrabold text-black">Partai</p>
        <p class="text-2xl font-extrabold text-black" id="peserta-partai">-</p>
    </div>

    <!-- Sudut Merah (Kanan) -->
    <div class="flex items-center gap-4">
        <div>
            <p class="text-sm font-bold text-red-700 text-right" id="peserta-nama-merah">-</p>
            <p class="text-xs text-gray-500 text-right" id="peserta-kontingen-merah">-</p>
        </div>
        <div class="w-9 h-9 rounded-full border-2 border-red-600 bg-white flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}" class="w-5 h-5 object-contain">
        </div>
        <div class="w-12 h-7 border overflow-hidden shadow rounded-md">
            <div class="h-1/2 bg-red-600"></div>
            <div class="h-1/2 bg-white"></div>
        </div>
    </div>
</div>
