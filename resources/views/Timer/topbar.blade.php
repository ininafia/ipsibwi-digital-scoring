<div class="bg-gray-200 rounded-xl shadow flex items-center justify-between px-8 py-4">

    <!-- LEFT -->
    <div class="flex items-center gap-4">

        <!-- FLAG -->
        <div class="w-20 h-12 overflow-hidden rounded shadow">
            <div class="h-1/2 bg-red-600"></div>
            <div class="h-1/2 bg-white"></div>
        </div>

        <!-- ICON -->
        <div class="w-16 h-16 rounded-full border-4 border-red-600 bg-white flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}"
                 alt="Fighter"
                 class="w-12 h-12 object-contain">
        </div>

    </div>

    <!-- CENTER -->
    <div class="text-center">
        <h1 class="text-2xl font-bold">Partai {{ $match->partai ?? '-' }}</h1>
        <p class="text-xl mt-1">Kelas {{ $match->kelas ?? '-' }} {{ $match->jenis_kelamin ?? '' }} {{ $match->golongan ?? '' }}</p>
    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-4">

        <!-- ICON -->
        <div class="w-16 h-16 rounded-full border-4 border-blue-600 bg-white flex items-center justify-center overflow-hidden">
            <img src="{{ asset('images/icons/man.png') }}"
                 alt="Fighter"
                 class="w-12 h-12 object-contain">
        </div>

        <!-- FLAG -->
        <div class="w-20 h-12 overflow-hidden rounded shadow">
            <div class="h-1/2 bg-red-600"></div>
            <div class="h-1/2 bg-white"></div>
        </div>

    </div>

</div>