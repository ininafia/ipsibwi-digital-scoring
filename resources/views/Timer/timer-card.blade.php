<div class="bg-gray-200 rounded-2xl shadow-md p-4 flex flex-col justify-between">

    <!-- TITLE -->
    <div class="text-center">

        <h2 class="text-4xl font-extrabold">
            TIME
        </h2>

        <!-- TIMER -->
        <div id="timer-display" class="text-4xl font-light mt-6 tracking-widest">
            02 : 00
        </div>

        <p class="text-2xl font-bold mt-3">
            MINUTE
        </p>

    </div>

    <!-- BUTTON -->
    <div class="flex justify-center items-center gap-4 mt-10 flex-wrap">

        <!-- START -->
        <button id="btn-timer-start"
            class="bg-green-500 hover:bg-green-600 transition
                   text-white font-bold text-lg
                   px-6 py-3 rounded-xl
                   min-w-[120px]">
            ▶ Start
        </button>

        <!-- PAUSE -->
        <button id="btn-timer-pause"
            class="bg-yellow-400 hover:bg-yellow-500 transition
                   text-black font-bold text-lg
                   px-6 py-3 rounded-xl
                   min-w-[120px]">
            ⏸ Pause
        </button>

        <!-- RESET -->
        <button id="btn-timer-reset"
            class="bg-red-600 hover:bg-red-700 transition
                   text-white font-bold text-lg
                   px-6 py-3 rounded-xl
                   min-w-[120px]">
            ↻ Reset
        </button>

    </div>

</div>