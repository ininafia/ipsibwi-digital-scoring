<section class="bg-gray-200 rounded-xl shadow p-2 sm:p-3">

    <div class="grid grid-cols-3 items-center gap-2 sm:gap-4">

        <!-- =========================
             PANEL BIRU
        ========================== -->
        <div class="flex items-center justify-center sm:justify-start gap-2 sm:gap-3 w-full">

            <!-- TOMBOL PUKULAN & TENDANGAN -->
            <div class="flex flex-col gap-2 flex-1 max-w-[150px]">
                <button
                    onclick="addScore('biru', 1)"
                    class="w-full h-10 sm:h-12 bg-blue-700 hover:bg-blue-800
                           rounded-lg text-white font-bold text-[10px] sm:text-xs
                           shadow transition flex items-center
                           justify-center gap-1 sm:gap-2 active:scale-95 px-2">
                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-5 h-5 sm:w-6 sm:h-6 object-contain">
                    <span>PUKULAN</span>
                </button>

                <button
                    onclick="addScore('biru', 2)"
                    class="w-full h-10 sm:h-12 bg-blue-700 hover:bg-blue-800
                           rounded-lg text-white font-bold text-[10px] sm:text-xs
                           shadow transition flex items-center
                           justify-center gap-1 sm:gap-2 active:scale-95 px-2">
                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-5 h-5 sm:w-6 sm:h-6 object-contain">
                    <span>TENDANGAN</span>
                </button>
            </div>

            <!-- DELETE SCORE -->
            <button
                onclick="deleteScore('biru')"
                class="w-20 sm:w-24 h-10 sm:h-12 bg-blue-700 hover:bg-blue-800
                       rounded-lg text-white font-bold text-[9px] sm:text-[10px] leading-tight
                       shadow transition flex flex-col items-center justify-center
                       active:scale-95 px-1 text-center shrink-0 self-center">
                <i class="fa-solid fa-rotate-left text-xs mb-0.5"></i>
                <span>DELETE SCORE</span>
            </button>

        </div>

        <!-- =========================
             INFO JURI
        ========================== -->
        <div class="text-center px-1">
            <img
                src="{{ asset('images/logos/LOGO IPSI.png') }}"
                class="w-7 h-7 sm:w-9 sm:h-9 mx-auto mb-1 object-contain"
                alt="Logo IPSI">

            <h2 id="juri-nama-posisi" class="text-base sm:text-xl font-extrabold text-black leading-tight truncate">
                {{ $namaPosisi }}
            </h2>

            <p id="juri-nama-petugas" class="text-xs sm:text-sm font-bold text-black truncate">
                {{ $namaPetugas }}
            </p>
        </div>

        <!-- =========================
             PANEL MERAH
        ========================== -->
        <div class="flex items-center justify-center sm:justify-end gap-2 sm:gap-3 w-full">

            <!-- DELETE SCORE -->
            <button
                onclick="deleteScore('merah')"
                class="w-20 sm:w-24 h-10 sm:h-12 bg-red-600 hover:bg-red-700
                       rounded-lg text-white font-bold text-[9px] sm:text-[10px] leading-tight
                       shadow transition flex flex-col items-center justify-center
                       active:scale-95 px-1 text-center shrink-0 self-center">
                <i class="fa-solid fa-rotate-left text-xs mb-0.5"></i>
                <span>DELETE SCORE</span>
            </button>

            <!-- TOMBOL PUKULAN & TENDANGAN -->
            <div class="flex flex-col gap-2 flex-1 max-w-[150px]">
                <button
                    onclick="addScore('merah', 1)"
                    class="w-full h-10 sm:h-12 bg-red-600 hover:bg-red-700
                           rounded-lg text-white font-bold text-[10px] sm:text-xs
                           shadow transition flex items-center
                           justify-center gap-1 sm:gap-2 active:scale-95 px-2">
                    <img
                        src="{{ asset('images/icons/pukul 1.png') }}"
                        alt="Pukulan"
                        class="w-5 h-5 sm:w-6 sm:h-6 object-contain">
                    <span>PUKULAN</span>
                </button>

                <button
                    onclick="addScore('merah', 2)"
                    class="w-full h-10 sm:h-12 bg-red-600 hover:bg-red-700
                           rounded-lg text-white font-bold text-[10px] sm:text-xs
                           shadow transition flex items-center
                           justify-center gap-1 sm:gap-2 active:scale-95 px-2">
                    <img
                        src="{{ asset('images/icons/tendang 2.png') }}"
                        alt="Tendangan"
                        class="w-5 h-5 sm:w-6 sm:h-6 object-contain">
                    <span>TENDANGAN</span>
                </button>
            </div>

        </div>

    </div>

</section>