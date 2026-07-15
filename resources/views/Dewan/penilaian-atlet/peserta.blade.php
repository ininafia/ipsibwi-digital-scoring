<div class="bg-gray-200 rounded-lg p-4 mb-4 flex justify-between items-center shadow border border-gray-300">
    <!-- Sudut Kiri -->
    <div class="flex items-center gap-4 w-1/3">
        <!-- Bendera -->
        <div class="w-12 h-7 border overflow-hidden shadow rounded-md">
            <div class="h-1/2 bg-red-600"></div>
            <div class="h-1/2 bg-white"></div>
        </div>
        <!-- Avatar/Icon -->
        <div class="w-9 h-9 rounded-full border-2 border-red-600 bg-white flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}" alt="Atlet" class="w-5 h-5 object-contain">
        </div>
        <!-- Info Atlet -->
        <div class="text-left">
            <div class="text-red-600 font-bold text-[16px] leading-tight">{{ $pertandingan ? $pertandingan->sudut_merah : 'Nama Atlet' }}</div>
            <div class="text-black font-bold text-[13px]">{{ $pertandingan ? $pertandingan->kontingen_merah : 'Asal Kontingen' }}</div>
        </div>
    </div>

    <!-- Tengah (Partai) -->
    <div class="text-center font-bold text-lg w-1/3">
        <div class="text-black">Partai</div>
        <div id="peserta-partai" class="text-black">{{ $pertandingan ? $pertandingan->partai : '-' }}</div>
    </div>

    <!-- Sudut Kanan -->
    <div class="flex items-center justify-end gap-4 w-1/3">
        <!-- Info Atlet -->
        <div class="text-right">
            <div class="text-blue-700 font-bold text-[16px] leading-tight">{{ $pertandingan ? $pertandingan->sudut_biru : 'Nama Atlet' }}</div>
            <div class="text-black font-bold text-[13px]">{{ $pertandingan ? $pertandingan->kontingen_biru : 'Asal Kontingen' }}</div>
        </div>
        <!-- Avatar/Icon -->
        <div class="w-9 h-9 rounded-full border-2 border-blue-700 bg-white flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}" alt="Atlet" class="w-5 h-5 object-contain">
        </div>
        <!-- Bendera -->
        <div class="w-12 h-7 border overflow-hidden shadow rounded-md">
            <div class="h-1/2 bg-red-600"></div>
            <div class="h-1/2 bg-white"></div>
        </div>
    </div>
</div>
