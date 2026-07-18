<section class="bg-gray-200 rounded-xl shadow p-3 mb-3">

    <div class="grid grid-cols-3 items-center gap-2">

        <!-- =========================
             PESERTA BIRU
        ========================== -->
        <div class="flex items-center gap-2">

            <!-- BENDERA -->
            <div class="w-12 h-7 border overflow-hidden shadow rounded-md">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>

            <!-- ICON -->
            <div class="w-9 h-9 rounded-full border-2 border-blue-600 bg-white flex items-center justify-center overflow-hidden">

                <img
                    src="{{ asset('images/icons/man.png') }}"
                    alt="Atlet"
                    class="w-5 h-5 object-contain">

            </div>

            <!-- NAMA -->
            <div class="leading-tight">

                <h2 id="juri-nama-biru" class="text-blue-600 font-bold text-sm">
                    {{ $match->sudut_biru ?? 'Nama Atlet' }}
                </h2>

                <p id="juri-sekolah-biru" class="font-semibold text-xs text-black">
                    {{ $match->kontingen_biru ?? 'Asal Kontingen' }}
                </p>

            </div>

        </div>

        <!-- =========================
             PARTAI
        ========================== -->
        <div class="text-center">

            <h3 class="text-lg font-bold">
                Partai
            </h3>

            <p id="juri-partai" class="text-xl font-semibold">
                {{ $match->partai ?? '-' }}
            </p>

        </div>

        <!-- =========================
             PESERTA MERAH
        ========================== -->
        <div class="flex items-center justify-end gap-2">

            <!-- NAMA -->
            <div class="leading-tight text-right">

                <h2 id="juri-nama-merah" class="text-red-600 font-bold text-sm">
                    {{ $match->sudut_merah ?? 'Nama Atlet' }}
                </h2>

                <p id="juri-sekolah-merah" class="font-semibold text-xs text-black">
                    {{ $match->kontingen_merah ?? 'Asal Kontingen' }}
                </p>

            </div>

            <!-- ICON -->
            <div class="w-9 h-9 rounded-full border-2 border-red-600 bg-white flex items-center justify-center overflow-hidden">

                <img
                    src="{{ asset('images/icons/man.png') }}"
                    alt="Atlet"
                    class="w-5 h-5 object-contain">

            </div>

            <!-- BENDERA -->
            <div class="w-12 h-7 border overflow-hidden shadow rounded-md">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>

        </div>

    </div>

</section>