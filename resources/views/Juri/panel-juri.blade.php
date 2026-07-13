<div class="bg-gray-200 rounded-lg p-4 shadow border border-gray-300">
    <div class="flex justify-between items-start gap-2 md:gap-4">
        
        <!-- Tombol Sudut Kiri (Biru) -->
        <div class="flex flex-col gap-2 w-[35%]">
            <div class="flex gap-2">
                <button onclick="addScore('biru', 1, this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-1 md:px-2 rounded flex-1 text-[10px] md:text-[12px] shadow transition-all active:scale-95 flex flex-col items-center justify-center gap-1">
                    <img src="{{ asset('images/icons/pukul 1.png') }}" alt="Pukulan" class="w-5 h-5 md:w-6 md:h-6 object-contain">
                    <span>PUKULAN</span>
                </button>
                <button onclick="addScore('biru', 2, this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-1 md:px-2 rounded flex-1 text-[10px] md:text-[12px] shadow transition-all active:scale-95 flex flex-col items-center justify-center gap-1">
                    <img src="{{ asset('images/icons/tendang 2.png') }}" alt="Tendangan" class="w-5 h-5 md:w-6 md:h-6 object-contain">
                    <span>TENDANGAN</span>
                </button>
            </div>
            <div class="flex gap-2">
                <button onclick="deleteScore('biru', this)" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[10px] md:text-[12px] shadow transition-all active:scale-95 border border-gray-700">
                    DEL SCORE
                </button>
            </div>
        </div>

        <!-- Bagian Tengah (Info Juri) -->
        <div class="flex flex-col items-center justify-between w-[30%] h-full mt-2">
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-[50px] h-[50px] md:w-[70px] md:h-[70px] object-contain mb-4 md:mb-8">
            <h2 class="text-sm md:text-lg font-extrabold text-black leading-tight text-center">
                {{ $namaPosisi }}
            </h2>
            <p class="text-[10px] md:text-sm font-bold text-black text-center mt-1">
                {{ $namaPetugas }}
            </p>
        </div>

        <!-- Tombol Sudut Kanan (Merah) -->
        <div class="flex flex-col gap-2 w-[35%] items-end">
            <div class="flex gap-2 w-full justify-end">
                <button onclick="addScore('merah', 1, this)" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-1 md:px-2 rounded flex-1 text-[10px] md:text-[12px] shadow transition-all active:scale-95 flex flex-col items-center justify-center gap-1">
                    <img src="{{ asset('images/icons/pukul 1.png') }}" alt="Pukulan" class="w-5 h-5 md:w-6 md:h-6 object-contain">
                    <span>PUKULAN</span>
                </button>
                <button onclick="addScore('merah', 2, this)" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-1 md:px-2 rounded flex-1 text-[10px] md:text-[12px] shadow transition-all active:scale-95 flex flex-col items-center justify-center gap-1">
                    <img src="{{ asset('images/icons/tendang 2.png') }}" alt="Tendangan" class="w-5 h-5 md:w-6 md:h-6 object-contain">
                    <span>TENDANGAN</span>
                </button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button onclick="deleteScore('merah', this)" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[10px] md:text-[12px] shadow transition-all active:scale-95 border border-gray-700">
                    DEL SCORE
                </button>
            </div>
        </div>

    </div>
</div>