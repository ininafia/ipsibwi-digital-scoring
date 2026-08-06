<!-- Bagian Atas: Skor Utama (Biru & Merah) -->
<div class="flex flex-1 overflow-hidden">
    
    <!-- Skor Biru -->
    <div class="w-[45%] bg-[#0000dd] flex items-center justify-center">
        <span id="skor_biru" class="text-white font-bold leading-none text-[5rem] sm:text-[6.5rem] lg:text-[8rem]">0</span>
    </div>

    <!-- Ronde di Tengah -->
    <div class="w-[10%] bg-white flex flex-col border-x-[2px] border-black">
        <div class="border-b-[2px] border-black flex items-center justify-center text-[10px] lg:text-xs font-bold py-1">ROUND</div>
        <div id="box-round-1" class="flex-[1] border-b-[2px] border-black flex items-center justify-center text-lg sm:text-xl lg:text-2xl font-bold">1</div>
        <div id="box-round-2" class="flex-[1] border-b-[2px] border-black flex items-center justify-center text-lg sm:text-xl lg:text-2xl font-bold">2</div>
        <div id="box-round-3" class="flex-[1] border-b-[2px] border-black flex items-center justify-center text-lg sm:text-xl lg:text-2xl font-bold">3</div>
        <div class="flex-[1.2] flex items-center justify-center p-0.5">
            <!-- Logo IPSI -->
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="IPSI" class="w-10 sm:w-12 lg:w-14 h-10 sm:h-12 lg:h-14 object-contain">
        </div>
    </div>

    <!-- Skor Merah -->
    <div class="w-[45%] bg-[#cc0000] flex items-center justify-center">
        <span id="skor_merah" class="text-white font-bold leading-none text-[5rem] sm:text-[6.5rem] lg:text-[8rem]">0</span>
    </div>

</div>

<!-- Bagian Bawah: Skor Juri -->
<div class="h-[60px] sm:h-[70px] lg:h-[80px] border-t-[2px] border-black bg-white flex flex-col font-medium text-xs lg:text-sm shrink-0">
    
    <!-- Baris Juri Atas (Pukulan) -->
    <div class="flex flex-1 border-b-[2px] border-black items-center">
        <div class="w-[45%] flex justify-around items-center px-1 lg:px-4">
            <span id="vote-blue-punch-J1" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J1</span>
            <span id="vote-blue-punch-J2" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J2</span>
            <span id="vote-blue-punch-J3" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J3</span>
        </div>
        <div class="w-[10%] border-x-[2px] border-black flex items-center justify-center h-full" title="Pukulan">
            <img src="{{ asset('images/icons/pukul 1.png') }}" alt="Pukulan" class="h-3.5 sm:h-4 lg:h-5 w-auto object-contain brightness-0">
        </div>
        <div class="w-[45%] flex justify-around items-center px-1 lg:px-4">
            <span id="vote-red-punch-J1" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J1</span>
            <span id="vote-red-punch-J2" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J2</span>
            <span id="vote-red-punch-J3" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J3</span>
        </div>
    </div>

    <!-- Baris Juri Bawah (Tendangan) -->
    <div class="flex flex-1 items-center">
        <div class="w-[45%] flex justify-around items-center px-1 lg:px-4">
            <span id="vote-blue-kick-J1" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J1</span>
            <span id="vote-blue-kick-J2" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J2</span>
            <span id="vote-blue-kick-J3" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J3</span>
        </div>
        <div class="w-[10%] border-x-[2px] border-black flex items-center justify-center h-full" title="Tendangan">
            <img src="{{ asset('images/icons/tendang 2.png') }}" alt="Tendangan" class="h-3.5 sm:h-4 lg:h-5 w-auto object-contain brightness-0">
        </div>
        <div class="w-[45%] flex justify-around items-center px-1 lg:px-4">
            <span id="vote-red-kick-J1" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J1</span>
            <span id="vote-red-kick-J2" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J2</span>
            <span id="vote-red-kick-J3" class="transition-colors duration-150 px-2 py-0.5 rounded font-bold">J3</span>
        </div>
    </div>

</div>
