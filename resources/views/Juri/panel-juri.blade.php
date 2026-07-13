<section class="bg-gray-200 rounded-xl shadow p-3">

    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">

        <!-- =========================
             INFO JURI
        ========================== -->
        <div class="order-1 md:order-2 text-center mb-4 md:mb-0">

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
             PANEL BIRU
        ========================== -->
        <div class="order-2 md:order-1 flex flex-col sm:flex-row items-center gap-2 justify-center md:justify-start">

            <!-- TOMBOL -->
            <div class="grid grid-cols-2 sm:grid-cols-1 gap-2">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('biru', 1)"
                    class="w-full sm:w-36 h-14 px-2 bg-blue-700 hover:bg-blue-800
                           rounded-md text-white font-bold text-[11px]
                           shadow transition flex items-center
                           justify-center gap-2">

                    <!-- ICON -->
                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-6 h-6 sm:w-7 sm:h-7 object-contain">

                    <!-- TEXT -->
                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('biru', 2)"
                    class="w-full sm:w-36 h-14 px-2 bg-blue-700 hover:bg-blue-800
                           rounded-md text-white font-bold text-[11px]
                           shadow transition flex items-center
                           justify-center gap-2">

                    <!-- ICON -->
                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-6 h-6 sm:w-7 sm:h-7 object-contain">

                    <!-- TEXT -->
                    <span>TENDANGAN</span>

                </button>

            </div>

            <!-- DELETE -->
            <button
                onclick="deleteScore('biru')"
                class="w-full sm:w-36 h-14 bg-blue-700 hover:bg-blue-800
                       rounded-md text-white font-bold text-[11px]
                       shadow transition flex items-center
                       justify-center mt-2 sm:mt-0">

                <span>DELETE SCORE</span>

            </button>

        </div>

        <!-- =========================
             PANEL MERAH
        ========================== -->
        <div class="order-3 md:order-3 flex flex-col sm:flex-row items-center justify-center md:justify-end gap-2 mt-4 md:mt-0">

            <!-- DELETE -->
            <button
                onclick="deleteScore('merah')"
                class="w-full sm:w-36 h-14 bg-red-600 hover:bg-red-700
                       rounded-md text-white font-bold text-[11px]
                       shadow transition flex items-center
                       justify-center order-2 sm:order-1 mt-2 sm:mt-0">

                <span>DELETE SCORE</span>

            </button>

            <!-- TOMBOL -->
            <div class="grid grid-cols-2 sm:grid-cols-1 gap-2 order-1 sm:order-2 w-full sm:w-auto">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('merah', 1)"
                    class="w-full sm:w-36 h-14 px-2 bg-red-600 hover:bg-red-700
                           rounded-md text-white font-bold text-[11px]
                           shadow transition flex items-center
                           justify-center gap-2">

                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-6 h-6 sm:w-7 sm:h-7 object-contain">

                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('merah', 2)"
                    class="w-full sm:w-36 h-14 px-2 bg-red-600 hover:bg-red-700
                           rounded-md text-white font-bold text-[11px]
                           shadow transition flex items-center
                           justify-center gap-2">

                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-6 h-6 sm:w-7 sm:h-7 object-contain">

                    <span>TENDANGAN</span>

                </button>

            </div>

        </div>

    </div>

</section>