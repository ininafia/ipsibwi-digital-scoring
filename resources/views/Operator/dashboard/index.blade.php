@extends('Operator.layout.app')

@section('content')

    {{-- TITLE --}}
    <h1 class="text-[32px] font-bold text-[#59d0ff] leading-none">
        Dashboard Kategori Tanding
    </h1>

    {{-- SUBTITLE --}}
    <p class="mt-4 text-[16px] text-black font-normal">
        Selamat datang di halaman Dashboard Tanding.
        Gunakan menu disamping untuk navigasi.
    </p>

    {{-- CARD MENU --}}
    <div class="flex gap-7 mt-14">

        {{-- CARD OPERATOR --}}
        <a 
            href="{{ route('operator.tanding.index') }}"
            class="w-[170px] rounded-[18px] bg-[#fdfdfd] border border-gray-300 shadow-md overflow-hidden hover:scale-105 transition duration-300 cursor-pointer"
        >

            {{-- ICON --}}
            <div class="h-[110px] flex items-center justify-center">

                <i class="fa-solid fa-desktop text-[40px] text-[#57d2ff]"></i>

            </div>

            {{-- FOOTER --}}
            <div class="border-t border-gray-300 py-3 text-center">

                <h2 class="text-[#b7b7b7] text-[15px] font-medium">
                    Operator
                </h2>

            </div>

        </a>

        {{-- CARD MONITOR DISPLAY --}}
        <a href="{{ route('operator.monitor-display') }}"
            class="block w-[170px] rounded-[18px] bg-[#fdfdfd] border border-gray-300 shadow-md overflow-hidden hover:scale-105 transition duration-300 cursor-pointer">

            {{-- ICON --}}
            <div class="h-[110px] flex items-center justify-center">

                <i class="fa-solid fa-video text-[40px] text-[#57d2ff]"></i>

            </div>

            {{-- FOOTER --}}
            <div class="border-t border-gray-300 py-3 text-center">

                <h2 class="text-[#b7b7b7] text-[15px] font-medium">
                    Monitor Display
                </h2>

            </div>

        </a>

    </div>

@endsection 