<section class="bg-gray-200 rounded-xl shadow p-2 md:p-3 mb-2 md:mb-3">

    <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-3 sm:gap-2">

        <!-- =========================
             PESERTA MERAH
        ========================== -->
        <div class="flex items-center justify-center sm:justify-start gap-2 order-2 sm:order-1">

            <!-- BENDERA -->
            <div class="w-10 h-6 md:w-12 md:h-7 border overflow-hidden shadow rounded-md shrink-0">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>

            <!-- ICON -->
            <div class="w-8 h-8 md:w-9 md:h-9 rounded-full border-2 border-red-600 bg-white flex items-center justify-center overflow-hidden shrink-0">

                <img
                    src="{{ asset('images/icons/man.png') }}"
                    alt="Atlet"
                    class="w-4 h-4 md:w-5 md:h-5 object-contain">

            </div>

            <!-- NAMA -->
            <div class="leading-tight text-center sm:text-left">

                <h2 id="juri-nama-merah" class="text-red-600 font-bold text-xs md:text-sm">
                    {{ $match->sudut_merah ?? 'Nama Atlet' }}
                </h2>

                <p id="juri-sekolah-merah" class="font-semibold text-[10px] md:text-xs text-black">
                    {{ $match->kontingen_merah ?? 'Asal Kontingen' }}
                </p>

            </div>

        </div>

        <!-- =========================
             PARTAI
        ========================== -->
        <div class="text-center order-1 sm:order-2">

            <h3 class="text-sm md:text-lg font-bold">
                Partai
            </h3>

            <p id="juri-partai" class="text-base md:text-xl font-semibold">
                {{ $match->partai ?? '-' }}
            </p>

        </div>

        <!-- =========================
             PESERTA BIRU
        ========================== -->
        <div class="flex items-center justify-center sm:justify-end gap-2 order-3 sm:order-3">

            <!-- NAMA -->
            <div class="leading-tight text-center sm:text-right">

                <h2 id="juri-nama-biru" class="text-blue-600 font-bold text-xs md:text-sm">
                    {{ $match->sudut_biru ?? 'Nama Atlet' }}
                </h2>

                <p id="juri-sekolah-biru" class="font-semibold text-[10px] md:text-xs text-black">
                    {{ $match->kontingen_biru ?? 'Asal Kontingen' }}
                </p>

            </div>

            <!-- ICON -->
            <div class="w-8 h-8 md:w-9 md:h-9 rounded-full border-2 border-blue-600 bg-white flex items-center justify-center overflow-hidden shrink-0">

                <img
                    src="{{ asset('images/icons/man.png') }}"
                    alt="Atlet"
                    class="w-4 h-4 md:w-5 md:h-5 object-contain">

            </div>

            <!-- BENDERA -->
            <div class="w-10 h-6 md:w-12 md:h-7 border overflow-hidden shadow rounded-md shrink-0">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>

        </div>

    </div>

</section>