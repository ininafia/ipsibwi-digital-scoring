<section class="bg-gray-200 rounded-xl shadow p-3">

    <div class="grid grid-cols-3 items-center gap-3">

        <!-- =========================
             PANEL BIRU
        ========================== -->
        <div class="flex items-center gap-2">

            <!-- TOMBOL -->
            <div class="grid grid-cols-1 gap-2">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('biru', 1)"
                    class="w-36 h-14 bg-blue-700 hover:bg-blue-800 active:scale-95 transition-all duration-150
                           rounded-md text-white font-bold text-[11px]
                           shadow flex items-center
                           justify-center gap-2">

                    <!-- ICON -->
                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-7 h-7 object-contain">

                    <!-- TEXT -->
                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('biru', 2)"
                    class="w-36 h-14 bg-blue-700 hover:bg-blue-800 active:scale-95 transition-all duration-150
                           rounded-md text-white font-bold text-[11px]
                           shadow flex items-center
                           justify-center gap-2">

                    <!-- ICON -->
                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-7 h-7 object-contain">

                    <!-- TEXT -->
                    <span>TENDANGAN</span>

                </button>

            </div>

            <!-- DELETE -->
            <button
                onclick="deleteScore('biru')"
                class="w-36 h-14 bg-blue-700 hover:bg-blue-800 active:scale-95 transition-all duration-150
                       rounded-md text-white font-bold text-[11px]
                       shadow flex items-center
                       justify-center">

                <span>DELETE SCORE</span>

            </button>

        </div>

        <!-- =========================
             INFO JURI
        ========================== -->
        <div class="text-center">

            <img
                src="{{ asset('images/logos/LOGO IPSI.png') }}"
                class="w-10 h-10 mx-auto mb-1 object-contain"
                alt="Logo IPSI">

            <h2 class="text-xl font-extrabold text-black leading-tight">
                {{ $namaPosisi }}
            </h2>

            <p class="text-sm font-bold text-black">
                {{ $namaPetugas }}
            </p>

        </div>

        <!-- =========================
             PANEL MERAH
        ========================== -->
        <div class="flex items-center justify-end gap-2">

            <!-- DELETE -->
            <button
                onclick="deleteScore('merah')"
                class="w-36 h-14 bg-red-600 hover:bg-red-700 active:scale-95 transition-all duration-150
                       rounded-md text-white font-bold text-[11px]
                       shadow flex items-center
                       justify-center">

                <span>DELETE SCORE</span>

            </button>

            <!-- TOMBOL -->
            <div class="grid grid-cols-1 gap-2">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('merah', 1)"
                    class="w-36 h-14 bg-red-600 hover:bg-red-700 active:scale-95 transition-all duration-150
                           rounded-md text-white font-bold text-[11px]
                           shadow flex items-center
                           justify-center gap-2">

                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-7 h-7 object-contain">

                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('merah', 2)"
                    class="w-36 h-14 bg-red-600 hover:bg-red-700 active:scale-95 transition-all duration-150
                           rounded-md text-white font-bold text-[11px]
                           shadow flex items-center
                           justify-center gap-2">

                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-7 h-7 object-contain">

                    <span>TENDANGAN</span>

                </button>

            </div>

        </div>

    </div>

</section>