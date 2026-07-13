<section class="bg-gray-200 rounded-xl shadow p-2 md:p-3">

    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 md:gap-3">

        <!-- =========================
             PANEL BIRU
        ========================== -->
        <div class="flex flex-row items-stretch gap-2 order-2 md:order-1 w-full justify-center md:justify-start">

            <!-- TOMBOL (Kiri) -->
            <div class="flex flex-col gap-2 w-1/2 xl:w-40">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('biru', 1, this)"
                    class="w-full h-12 md:h-14 bg-blue-700 hover:bg-blue-800
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition-all active:scale-95 flex items-center
                           justify-center gap-1 md:gap-2">

                    <!-- ICON -->
                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-5 h-5 md:w-7 md:h-7 object-contain">

                    <!-- TEXT -->
                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('biru', 2, this)"
                    class="w-full h-12 md:h-14 bg-blue-700 hover:bg-blue-800
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition-all active:scale-95 flex items-center
                           justify-center gap-1 md:gap-2">

                    <!-- ICON -->
                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-5 h-5 md:w-7 md:h-7 object-contain">

                    <!-- TEXT -->
                    <span>TENDANGAN</span>

                </button>

            </div>

            <!-- DELETE (Kanan) -->
            <button
                onclick="deleteScore('biru', this)"
                class="w-1/2 xl:w-32 h-auto bg-blue-700 hover:bg-blue-800
                       rounded-md text-white font-bold text-[10px] md:text-[11px]
                       shadow transition-all active:scale-95 flex items-center
                       justify-center">

                <span>DELETE SCORE</span>

            </button>

        </div>

        <!-- =========================
             INFO JURI
        ========================== -->
        <div class="text-center order-1 md:order-2">

            <img
                src="{{ asset('images/logos/LOGO IPSI.png') }}"
                class="w-8 h-8 md:w-10 md:h-10 mx-auto mb-1 object-contain"
                alt="Logo IPSI">

            <h2 class="text-lg md:text-xl font-extrabold text-black leading-tight">
                {{ $namaPosisi }}
            </h2>

            <p class="text-xs md:text-sm font-bold text-black">
                {{ $namaPetugas }}
            </p>

        </div>

        <!-- =========================
             PANEL MERAH
        ========================== -->
        <div class="flex flex-row items-stretch gap-2 order-3 md:order-3 w-full justify-center md:justify-end">

            <!-- DELETE (Kiri, Mirroring Biru) -->
            <button
                onclick="deleteScore('merah', this)"
                class="w-1/2 xl:w-32 h-auto bg-red-600 hover:bg-red-700
                       rounded-md text-white font-bold text-[10px] md:text-[11px]
                       shadow transition-all active:scale-95 flex items-center
                       justify-center">

                <span>DELETE SCORE</span>

            </button>

            <!-- TOMBOL (Kanan) -->
            <div class="flex flex-col gap-2 w-1/2 xl:w-40">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('merah', 1, this)"
                    class="w-full h-12 md:h-14 bg-red-600 hover:bg-red-700
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition-all active:scale-95 flex items-center
                           justify-center gap-1 md:gap-2">

                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-5 h-5 md:w-7 md:h-7 object-contain">

                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('merah', 2, this)"
                    class="w-full h-12 md:h-14 bg-red-600 hover:bg-red-700
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition-all active:scale-95 flex items-center
                           justify-center gap-1 md:gap-2">

                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-5 h-5 md:w-7 md:h-7 object-contain">

                    <span>TENDANGAN</span>

                </button>

            </div>
            
        </div>

    </div>

</section>