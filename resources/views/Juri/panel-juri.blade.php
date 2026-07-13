<section class="bg-gray-200 rounded-xl shadow p-2 md:p-3">

    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 md:gap-3">

        <!-- =========================
             PANEL BIRU
        ========================== -->
        <div class="flex flex-col xl:flex-row items-center gap-2 order-2 md:order-1 w-full justify-center md:justify-start">

            <!-- TOMBOL -->
            <div class="flex w-full gap-2 xl:grid xl:grid-cols-1 xl:w-auto">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('biru', 1)"
                    class="flex-1 xl:w-36 h-12 md:h-14 bg-blue-700 hover:bg-blue-800
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition flex items-center
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
                    onclick="addScore('biru', 2)"
                    class="flex-1 xl:w-36 h-12 md:h-14 bg-blue-700 hover:bg-blue-800
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition flex items-center
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

            <!-- DELETE -->
            <button
                onclick="deleteScore('biru')"
                class="w-full xl:w-36 h-10 xl:h-14 bg-blue-700 hover:bg-blue-800
                       rounded-md text-white font-bold text-[10px] md:text-[11px]
                       shadow transition flex items-center
                       justify-center mt-1 xl:mt-0">

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
        <div class="flex flex-col xl:flex-row items-center gap-2 order-3 md:order-3 w-full justify-center md:justify-end">

            <!-- DELETE -->
            <button
                onclick="deleteScore('merah')"
                class="w-full xl:w-36 h-10 xl:h-14 bg-red-600 hover:bg-red-700
                       rounded-md text-white font-bold text-[10px] md:text-[11px]
                       shadow transition flex items-center
                       justify-center mb-1 xl:mb-0 hidden xl:flex">

                <span>DELETE SCORE</span>

            </button>

            <!-- TOMBOL -->
            <div class="flex w-full gap-2 xl:grid xl:grid-cols-1 xl:w-auto">

                <!-- PUKULAN -->
                <button
                    onclick="addScore('merah', 1)"
                    class="flex-1 xl:w-36 h-12 md:h-14 bg-red-600 hover:bg-red-700
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition flex items-center
                           justify-center gap-1 md:gap-2">

                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-5 h-5 md:w-7 md:h-7 object-contain">

                    <span>PUKULAN</span>

                </button>

                <!-- TENDANGAN -->
                <button
                    onclick="addScore('merah', 2)"
                    class="flex-1 xl:w-36 h-12 md:h-14 bg-red-600 hover:bg-red-700
                           rounded-md text-white font-bold text-[10px] md:text-[11px]
                           shadow transition flex items-center
                           justify-center gap-1 md:gap-2">

                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-5 h-5 md:w-7 md:h-7 object-contain">

                    <span>TENDANGAN</span>

                </button>

            </div>
            
            <!-- DELETE MOBILE -->
            <button
                onclick="deleteScore('merah')"
                class="w-full xl:w-36 h-10 xl:h-14 bg-red-600 hover:bg-red-700
                       rounded-md text-white font-bold text-[10px] md:text-[11px]
                       shadow transition flex items-center
                       justify-center mt-1 xl:mt-0 xl:hidden">

                <span>DELETE SCORE</span>

            </button>

        </div>

    </div>

</section>