<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Scoring IPSI</title>

    @vite('resources/css/app.css')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-white text-black font-[Poppins]">

<div class="w-[95%] max-w-[1280px] mx-auto">

    {{-- NAVBAR --}}
    <nav class="py-4 md:h-[100px] flex flex-col sm:flex-row items-center justify-between gap-4">

        <div class="flex items-center justify-between w-full sm:w-auto">
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-[50px] sm:w-[70px]">
            <a href="{{ url('/login') }}"
               class="sm:hidden bg-[#75BFF0] text-white px-6 py-1.5 rounded-full text-base font-medium">
                Login
            </a>
        </div>

        <ul class="flex items-center gap-6 sm:gap-[55px] text-base sm:text-[21px] font-medium">
            <li><a href="{{ url('/') }}" class="hover:text-sky-500">Home</a></li>
            <li><a href="#kategori" class="hover:text-sky-500">Kategori</a></li>
        </ul>

        {{-- LOGIN BUTTON --}}
        <a href="{{ url('/login') }}"
           class="hidden sm:inline-block bg-[#75BFF0] text-white px-[42px] py-[8px] rounded-full text-[20px] font-medium hover:bg-sky-500 transition">
            Login
        </a>

    </nav>

    {{-- HERO --}}
    <section
        class="h-[320px] sm:h-[450px] md:h-[575px] rounded-[24px] sm:rounded-[35px] overflow-hidden bg-cover bg-center flex items-center justify-center px-4"
        style="background-image: linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35)), url({{ asset('images/background/pencak-silat.jpg') }});">

        <div class="text-center text-white">
            <h1 class="text-3xl sm:text-5xl md:text-[64px] font-extrabold italic tracking-[2px] sm:tracking-[3px] leading-tight">
                DIGITAL SCORING
            </h1>

            <p class="text-lg sm:text-2xl md:text-[34px] font-bold italic mt-2">
                Ikatan Pencak Silat Indonesia
            </p>
        </div>

    </section>

    {{-- KATEGORI --}}
    <section id="kategori" class="pt-8 sm:pt-[50px] pb-16 sm:pb-32 text-center">

        <h2 class="text-2xl sm:text-3xl md:text-[36px] font-bold mb-6 sm:mb-[30px]">
            Kategori Pertandingan
        </h2>

        <div class="flex flex-wrap justify-center gap-3 sm:gap-6 md:gap-8 lg:gap-12">

            {{-- TANDING --}}
            <a href="{{ url('/login') }}"
               class="w-[150px] sm:w-[180px] md:w-[200px] h-[140px] sm:h-[165px] md:h-[180px] bg-white rounded-[18px] sm:rounded-[22px] overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,.15)] block hover:scale-105 transition">

                <div class="h-[95px] sm:h-[115px] md:h-[125px] bg-[#AEEEFF] flex items-center justify-center">
                    <img src="{{ asset('images/icons/tanding.png') }}"
                         class="w-[45px] sm:w-[55px] md:w-[65px] h-[45px] sm:h-[55px] md:h-[65px] object-contain">
                </div>

                <p class="text-sm sm:text-base md:text-lg font-semibold mt-2 text-center text-gray-800">
                    Tanding
                </p>
            </a>

            {{-- TUNGGAL --}}
            <a href="#"
               class="w-[150px] sm:w-[180px] md:w-[200px] h-[140px] sm:h-[165px] md:h-[180px] bg-white rounded-[18px] sm:rounded-[22px] overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,.15)] block hover:scale-105 transition">

                <div class="h-[95px] sm:h-[115px] md:h-[125px] bg-[#AEEEFF] flex items-center justify-center">
                    <img src="{{ asset('images/icons/tunggal.png') }}"
                         class="w-[45px] sm:w-[55px] md:w-[65px] h-[45px] sm:h-[55px] md:h-[65px] object-contain">
                </div>

                <p class="text-sm sm:text-base md:text-lg font-semibold mt-2 text-center text-gray-800">
                    Tunggal
                </p>
            </a>

            {{-- GANDA --}}
            <a href="#"
               class="w-[150px] sm:w-[180px] md:w-[200px] h-[140px] sm:h-[165px] md:h-[180px] bg-white rounded-[18px] sm:rounded-[22px] overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,.15)] block hover:scale-105 transition">

                <div class="h-[95px] sm:h-[115px] md:h-[125px] bg-[#AEEEFF] flex items-center justify-center">
                    <img src="{{ asset('images/icons/ganda.png') }}"
                         class="w-[45px] sm:w-[55px] md:w-[65px] h-[45px] sm:h-[55px] md:h-[65px] object-contain">
                </div>

                <p class="text-sm sm:text-base md:text-lg font-semibold mt-2 text-center text-gray-800">
                    Ganda
                </p>
            </a>

        </div>

    </section>

</div>

</body>
</html>