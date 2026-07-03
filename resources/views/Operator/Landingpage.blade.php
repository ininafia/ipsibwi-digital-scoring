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

<div class="w-[95%] mx-auto">

    {{-- NAVBAR --}}
    <nav class="h-[100px] flex items-center justify-between">

        <div>
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-[70px]">
        </div>

        <ul class="flex gap-[55px] text-[21px] font-medium">
            <li><a href="{{ url('/') }}" class="hover:text-sky-500">Home</a></li>
            <li><a href="#kategori" class="hover:text-sky-500">Kategori</a></li>
        </ul>

        {{-- LOGIN BUTTON --}}
        <a href="{{ url('/login') }}"
           class="bg-[#75BFF0] text-white px-[42px] py-[8px] rounded-full text-[20px] font-medium">
            Login
        </a>

    </nav>

    {{-- HERO --}}
    <section
        class="h-[575px] rounded-[35px] overflow-hidden bg-cover bg-center flex items-center justify-center"
        style="background-image: linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35)), url({{ asset('images/background/pencak-silat.jpg') }});">

        <div class="text-center text-white">
            <h1 class="text-[64px] font-extrabold italic tracking-[3px] leading-tight">
                DIGITAL SCORING
            </h1>

            <p class="text-[34px] font-bold italic mt-2">
                Ikatan Pencak Silat Indonesia
            </p>
        </div>

    </section>

    {{-- KATEGORI --}}
    <section id="kategori" class="pt-[50px] pb-40 text-center">

        <h2 class="text-[40px] font-bold mb-[35px]">
            Kategori Pertandingan
        </h2>

        <div class="flex justify-center gap-[115px]">

            {{-- TANDING --}}
            <a href="{{ url('/login') }}"
               class="w-[245px] h-[205px] bg-white rounded-[24px] overflow-hidden shadow-[0_5px_4px_rgba(0,0,0,.25)] block hover:scale-105 transition">

                <div class="h-[145px] bg-[#AEEEFF] flex items-center justify-center">
                    <img src="{{ asset('images/icons/tanding.png') }}"
                         class="w-[75px] h-[75px] object-contain">
                </div>

                <p class="text-[21px] font-semibold mt-[15px] text-center">
                    Tanding
                </p>
            </a>

            {{-- TUNGGAL --}}
            <a href="#"
               class="w-[245px] h-[205px] bg-white rounded-[24px] overflow-hidden shadow-[0_5px_4px_rgba(0,0,0,.25)] block hover:scale-105 transition">

                <div class="h-[145px] bg-[#AEEEFF] flex items-center justify-center">
                    <img src="{{ asset('images/icons/tunggal.png') }}"
                         class="w-[75px] h-[75px] object-contain">
                </div>

                <p class="text-[21px] font-semibold mt-[15px] text-center">
                    Tunggal
                </p>
            </a>

            {{-- GANDA --}}
            <a href="#"
               class="w-[245px] h-[205px] bg-white rounded-[24px] overflow-hidden shadow-[0_5px_4px_rgba(0,0,0,.25)] block hover:scale-105 transition">

                <div class="h-[145px] bg-[#AEEEFF] flex items-center justify-center">
                    <img src="{{ asset('images/icons/ganda.png') }}"
                         class="w-[75px] h-[75px] object-contain">
                </div>

                <p class="text-[21px] font-semibold mt-[15px] text-center">
                    Ganda
                </p>
            </a>

        </div>

    </section>

</div>

</body>
</html>