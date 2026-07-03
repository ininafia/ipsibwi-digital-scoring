<div class="flex flex-col h-full bg-[#fdfdfd] text-center text-black">
    
    <!-- Binaan -->
    <div class="flex flex-col items-center justify-center flex-1 border-b-[2px] border-black py-1">
        <span class="text-sm lg:text-base font-bold mb-1">Binaan</span>
        <div class="flex gap-2 lg:gap-3">
            <div id="binaan-1-{{ $sudut ?? 'biru' }}" class="w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300">
                <img src="{{ asset('images/icons/binaan 1.png') }}" alt="Binaan 1" class="w-full h-full object-contain">
            </div>
            <div id="binaan-2-{{ $sudut ?? 'biru' }}" class="w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300">
                <img src="{{ asset('images/icons/binaan 2.png') }}" alt="Binaan 2" class="w-full h-full object-contain">
            </div>
        </div>
    </div>

    <!-- Teguran -->
    <div class="flex flex-col items-center justify-center flex-1 border-b-[2px] border-black py-1">
        <span class="text-sm lg:text-base font-bold mb-1">Teguran</span>
        <div class="flex gap-2 lg:gap-3">
            <div id="teguran-1-{{ $sudut ?? 'biru' }}" class="w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300">
                <img src="{{ asset('images/icons/teguran 1.png') }}" alt="Teguran 1" class="w-full h-full object-contain">
            </div>
            <div id="teguran-2-{{ $sudut ?? 'biru' }}" class="w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300">
                <img src="{{ asset('images/icons/teguran 2.png') }}" alt="Teguran 2" class="w-full h-full object-contain">
            </div>
        </div>
    </div>

    <!-- Peringatan -->
    <div class="flex flex-col items-center justify-center flex-1 border-b-[2px] border-black py-1">
        <span class="text-sm lg:text-base font-bold mb-1">Peringatan</span>
        <div class="flex gap-2 lg:gap-3">
            <div id="peringatan-1-{{ $sudut ?? 'biru' }}" class="w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300">
                <img src="{{ asset('images/icons/peringatan 1.png') }}" alt="Peringatan 1" class="w-full h-full object-contain">
            </div>
            <div id="peringatan-2-{{ $sudut ?? 'biru' }}" class="w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300">
                <img src="{{ asset('images/icons/peringatan 2.png') }}" alt="Peringatan 2" class="w-full h-full object-contain">
            </div>
        </div>
    </div>

    <!-- Jatuhan -->
    <div class="flex flex-col items-center justify-center flex-1 py-1">
        <span class="text-sm lg:text-base font-bold">Jatuhan</span>
        <div class="flex items-baseline mt-1">
            <span id="jatuhan-count-{{ $sudut ?? 'biru' }}" class="text-4xl lg:text-5xl font-black italic leading-none pr-1">0</span>
            <span class="text-sm lg:text-base font-bold">x</span>
        </div>
    </div>

</div>
