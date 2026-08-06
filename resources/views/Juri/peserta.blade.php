<section class="bg-gray-200 rounded-xl shadow p-2 sm:p-3 mb-2 sm:mb-3">

    <div class="grid grid-cols-3 items-center gap-1.5 sm:gap-2">

        <!-- =========================
             PESERTA BIRU
        ========================== -->
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-hidden">

            <!-- BENDERA -->
            <div class="hidden sm:block w-9 md:w-12 h-6 md:h-7 border overflow-hidden shadow rounded-md shrink-0">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>

            <!-- ICON -->
            <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full border-2 border-blue-600 bg-white flex items-center justify-center overflow-hidden shrink-0">
                <img
                    src="{{ asset('images/icons/man.png') }}"
                    alt="Atlet"
                    class="w-4 h-4 sm:w-5 sm:h-5 object-contain">
            </div>

            <!-- NAMA -->
            <div class="leading-tight min-w-0">
                <h2 id="juri-nama-biru" class="text-blue-600 font-bold text-xs sm:text-sm truncate">
                    {{ $match->sudut_biru ?? 'Nama Atlet' }}
                </h2>
                <p id="juri-sekolah-biru" class="font-semibold text-[10px] sm:text-xs text-black truncate">
                    {{ $match->kontingen_biru ?? 'Asal Kontingen' }}
                </p>
            </div>

        </div>

        <!-- =========================
             PARTAI
        ========================== -->
        <div class="text-center">
            <h3 class="text-sm sm:text-lg font-bold">
                Partai
            </h3>
            <p id="juri-partai" class="text-base sm:text-xl font-semibold">
                {{ $match->partai ?? '-' }}
            </p>
        </div>

        <!-- =========================
             PESERTA MERAH
        ========================== -->
        <div class="flex items-center justify-end gap-1.5 sm:gap-2 overflow-hidden">

            <!-- NAMA -->
            <div class="leading-tight text-right min-w-0">
                <h2 id="juri-nama-merah" class="text-red-600 font-bold text-xs sm:text-sm truncate">
                    {{ $match->sudut_merah ?? 'Nama Atlet' }}
                </h2>
                <p id="juri-sekolah-merah" class="font-semibold text-[10px] sm:text-xs text-black truncate">
                    {{ $match->kontingen_merah ?? 'Asal Kontingen' }}
                </p>
            </div>

            <!-- ICON -->
            <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full border-2 border-red-600 bg-white flex items-center justify-center overflow-hidden shrink-0">
                <img
                    src="{{ asset('images/icons/man.png') }}"
                    alt="Atlet"
                    class="w-4 h-4 sm:w-5 sm:h-5 object-contain">
            </div>

            <!-- BENDERA -->
            <div class="hidden sm:block w-9 md:w-12 h-6 md:h-7 border overflow-hidden shadow rounded-md shrink-0">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>

        </div>

    </div>

</section>