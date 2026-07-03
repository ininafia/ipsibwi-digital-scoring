@extends('Operator.layout.fullscreen')

@section('title', 'Detail Score')

@section('content')
<!-- FULL WIDTH NAVBAR -->
<div class="w-full bg-white flex justify-between items-center px-8 py-3 shadow-sm print:hidden">
    <div>
         <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="h-[60px] object-contain" onerror="this.style.display='none'">
    </div>
    <div>
        <button onclick="window.print()" class="bg-[#ffcc00] hover:bg-yellow-500 text-white font-bold py-2 px-8 rounded text-sm tracking-widest shadow-md">
            PRINT
        </button>
    </div>
</div>

<div class="relative max-w-[1200px] mx-auto p-5 bg-white shadow-[0_0_10px_rgba(0,0,0,0.1)] my-5 print:shadow-none print:m-0 print:max-w-full print:p-0">

    <!-- PARTICIPANT INFO -->
    <div class="flex justify-between items-center mb-4 px-2">
        <!-- Blue Corner Info -->
        <div class="flex items-center gap-4 w-1/3">
            <div class="w-14 h-14 bg-[#0000d0] rounded-sm"></div>
            <div class="leading-tight">
                <div class="text-[#0000d0] font-bold text-xl">Salsabila Ds</div>
                <div class="text-black font-semibold text-lg">SMP Darul Sholah</div>
            </div>
        </div>

        <!-- Center Info -->
        <div class="text-center w-1/3 leading-tight">
            <div class="font-bold text-xl tracking-wide">PARTAI  01</div>
            <div class="font-bold text-lg mt-1">TANDING - B REMAJA</div>
        </div>

        <!-- Red Corner Info -->
        <div class="flex items-center gap-4 justify-end w-1/3">
            <div class="text-right leading-tight">
                <div class="text-[#df0000] font-bold text-xl">Salsabila Ds</div>
                <div class="text-black font-semibold text-lg">SMP Ahmad Yani</div>
            </div>
            <div class="w-14 h-14 bg-[#df0000] rounded-sm"></div>
        </div>
    </div>

    <!-- SCORE TABLES -->
    @for($round = 1; $round <= 3; $round++)
    <table class="w-full border-collapse text-center border-2 border-black mt-[5px]">
        <thead>
            <tr>
                <th colspan="4" class="bg-[#0000d0] text-white border-2 border-black font-bold text-lg tracking-widest py-1 h-[35px]">BLUE CORNER</th>
                <th rowspan="2" class="font-bold border-l-2 border-r-2 border-black w-[6%] text-sm py-1 h-[35px] border-y border-y-black">ROUND</th>
                <th colspan="4" class="bg-[#df0000] text-white border-2 border-black font-bold text-lg tracking-widest py-1 h-[35px]">RED CORNER</th>
            </tr>
            <tr class="font-bold">
                <th class="w-[5%] font-bold py-1 border border-black p-1 h-[35px]">TOTAL</th>
                <th colspan="3" class="tracking-widest py-1 border border-black p-1 h-[35px]">DETAIL SCORE</th>
                <th colspan="3" class="tracking-widest py-1 border border-black p-1 h-[35px]">DETAIL SCORE</th>
                <th class="w-[5%] font-bold py-1 border border-black p-1 h-[35px]">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $labelClass = "w-[12%] font-bold border border-black p-1 h-[35px]";
                $wideClass = "w-[15%] border border-black p-1 h-[35px]";
                $narrowClass = "w-[5%] border border-black p-1 h-[35px]";
                $totalClass = "font-bold text-xl border-r-2 border-r-black border-y border-y-black border-l border-l-black p-1 h-[35px]";
                $totalRightClass = "font-bold text-xl border-l-2 border-l-black border-y border-y-black border-r border-r-black p-1 h-[35px]";
            @endphp
            <!-- Juri 1 -->
            <tr>
                <td rowspan="7" class="{{ $totalClass }}"></td> <!-- Total Blue -->
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">JURI 1</td>
                <td rowspan="7" class="font-bold border-l-2 border-r-2 border-y border-black w-[6%] text-2xl h-[35px]">{{ $round }}</td> <!-- Round Number -->
                <td class="{{ $labelClass }}">JURI 1</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
                <td rowspan="7" class="{{ $totalRightClass }}"></td> <!-- Total Red -->
            </tr>
            <!-- Juri 2 -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">JURI 2</td>
                <td class="{{ $labelClass }}">JURI 2</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- Juri 3 -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">JURI 3</td>
                <td class="{{ $labelClass }}">JURI 3</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- SCORE -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">SCORE</td>
                <td class="{{ $labelClass }}">SCORE</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- JATUHAN -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">JATUHAN</td>
                <td class="{{ $labelClass }}">JATUHAN</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- HUKUMAN -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">HUKUMAN</td>
                <td class="{{ $labelClass }}">HUKUMAN</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- BINAAN -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $labelClass }}">BINAAN</td>
                <td class="{{ $labelClass }}">BINAAN</td>
                <td class="{{ $wideClass }}"></td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
        </tbody>
    </table>
    @endfor

    <!-- DETAIL WINNER TABLE -->
    <table class="w-full border-collapse border-2 border-black mt-8 font-bold text-left">
        <thead>
            <tr>
                <th colspan="2" class="bg-black text-white border-2 border-black px-2 py-1.5 text-left pl-10 tracking-widest text-lg">DETAIL WINNER</th>
                <th colspan="8" class="bg-black text-white border-2 border-black px-2 py-1.5 text-left pl-[10%] tracking-widest text-lg border-l border-l-white">DETAIL WINNER</th>
            </tr>
        </thead>
        <tbody>
            @php
                $wtClass = "border border-black px-2 py-1.5 text-[0.9rem]";
            @endphp
            <tr>
                <td class="w-[12%] {{ $wtClass }}">Nama</td>
                <td class="w-[15%] {{ $wtClass }}">Salsabila</td>
                <td rowspan="5" class="{{ $wtClass }} bg-[#0000d0] text-white text-[5rem] text-center font-extrabold w-[12%] leading-none align-middle pb-[1rem]">8</td>
                <td rowspan="5" class="{{ $wtClass }} text-center w-[3%]">vs</td>
                <td rowspan="5" class="{{ $wtClass }} bg-[#df0000] text-white text-[5rem] text-center font-extrabold w-[12%] leading-none align-middle pb-[1rem]">8</td>
                <td class="w-[15%] {{ $wtClass }}">nama ...</td>
                <td class="w-[7%] {{ $wtClass }}"></td>
                <td class="w-[7%] {{ $wtClass }}"></td>
                <td class="w-[7%] {{ $wtClass }}"></td>
                <td class="w-[7%] {{ $wtClass }}"></td>
            </tr>
            <tr>
                <td class="{{ $wtClass }}">Kontingen</td>
                <td class="{{ $wtClass }}">SMP N 1 GIRI</td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
            </tr>
            <tr>
                <td class="{{ $wtClass }}">Kelas</td>
                <td class="{{ $wtClass }}">E PRA-REMAJA</td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
            </tr>
            <tr>
                <td class="{{ $wtClass }}">Sudut</td>
                <td class="{{ $wtClass }}">BIRU</td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
            </tr>
            <tr>
                <td class="{{ $wtClass }}">Winning By</td>
                <td class="{{ $wtClass }}">ANGKA</td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
            </tr>
            <tr>
                <td class="{{ $wtClass }}">Time Stemp</td>
                <td class="{{ $wtClass }}">2026-12-26 08:25</td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }} text-center">Ketua Pert</td>
                <td class="{{ $wtClass }} text-center">Dew Wasjur</td>
                <td class="{{ $wtClass }} text-center">Juri 1</td>
                <td class="{{ $wtClass }} text-center">Juri 2</td>
                <td class="{{ $wtClass }} text-center">Juri 3</td>
            </tr>
        </tbody>
    </table>
    
</div>
@endsection
