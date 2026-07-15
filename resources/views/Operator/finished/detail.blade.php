@extends('Operator.layout.fullscreen')

@section('title', 'Detail Score')

@section('content')
@php
function getBoxColor($awardId, $athlete, $sah) {
    if (!$sah) return '';
    if (!$awardId) return $athlete == 'blue' ? 'bg-[#0000d0] text-white' : 'bg-[#df0000] text-white';
    $colors = ['bg-[#ff3b8f] text-white', 'bg-[#ffcc00] text-black', 'bg-[#2d2d2d] text-white', 'bg-[#8b3dff] text-white', 'bg-[#10b981] text-white', 'bg-[#f97316] text-white', 'bg-[#0ea5e9] text-white', 'bg-[#eab308] text-white', 'bg-[#ec4899] text-white', 'bg-[#84cc16] text-white'];
    $hash = 0;
    for ($i = 0; $i < strlen($awardId); $i++) $hash = ord($awardId[$i]) + (($hash << 5) - $hash);
    return $colors[abs($hash) % count($colors)];
}
function renderEvents($events, $athlete) {
    if (empty($events)) return '';
    $html = '<div class="flex flex-wrap gap-1 justify-start items-center p-1">';
    foreach ($events as $evt) {
        if ($evt['sah']) {
            $html .= '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold rounded-sm ' . getBoxColor($evt['award_id'], $athlete, true) . '">' . $evt['value'] . '</span>';
        } else {
            $html .= '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold text-gray-500 line-through">' . $evt['value'] . '</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
function renderAwards($awards, $athlete) {
    if (empty($awards)) return '';
    $html = '<div class="flex flex-wrap gap-1 justify-start items-center p-1">';
    foreach ($awards as $awd) {
        $html .= '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold rounded-sm ' . getBoxColor($awd['award_id'], $athlete, true) . '">' . $awd['value'] . '</span>';
    }
    $html .= '</div>';
    return $html;
}
@endphp
<!-- FULL WIDTH NAVBAR -->
<div class="w-full bg-white flex justify-between items-center px-8 py-3 shadow-sm print:hidden">
    <div>
         <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="h-[60px] object-contain" onerror="this.style.display='none'">
    </div>
    <div class="flex items-center gap-4">
        <a href="javascript:history.back()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-1.5 px-4 rounded text-xs tracking-wider shadow-sm inline-flex items-center gap-1.5 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            KEMBALI
        </a>
        <a href="{{ route('operator.tanding.finished.export-pdf', $match->id) }}" class="bg-[#ffcc00] hover:bg-yellow-500 text-white font-bold py-2 px-8 rounded text-sm tracking-widest shadow-md inline-block">
            PRINT
        </a>
    </div>
</div>

<div class="relative max-w-[1200px] mx-auto p-8 bg-white shadow-xl rounded-xl my-8 transition-all duration-300 hover:shadow-2xl print:shadow-none print:rounded-none print:m-0 print:max-w-full print:p-0">

    <!-- PARTICIPANT INFO CARD -->
    <div class="flex justify-between items-center mb-8 px-6 py-5 bg-gray-50 rounded-xl border border-gray-100 shadow-sm print:bg-transparent print:border-none print:p-0 print:mb-4">
        <!-- Blue Corner Info -->
        <div class="flex items-center gap-5 w-1/3">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg shadow-md print:bg-[#0000d0]"></div>
            <div class="leading-tight">
                <div class="text-blue-700 font-extrabold text-xl print:text-[#0000d0]">{{ $match->sudut_biru ?? 'Nama Atlet' }}</div>
                <div class="text-gray-600 font-semibold text-lg print:text-black">{{ $match->kontingen_biru ?? 'Asal Kontingen' }}</div>
            </div>
        </div>

        <!-- Center Info -->
        <div class="text-center w-1/3 leading-tight bg-white px-4 py-3 rounded-lg shadow-sm border border-gray-100 print:border-none print:shadow-none">
            <div class="font-black text-2xl tracking-wider text-gray-800">PARTAI {{ str_pad($match->partai, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="font-semibold text-gray-500 mt-1 uppercase tracking-widest text-sm">TANDING - {{ $match->kelas }} {{ strtoupper($match->golongan) }}</div>
        </div>

        <!-- Red Corner Info -->
        <div class="flex items-center gap-5 justify-end w-1/3">
            <div class="text-right leading-tight">
                <div class="text-red-600 font-extrabold text-xl print:text-[#df0000]">{{ $match->sudut_merah ?? 'Nama Atlet' }}</div>
                <div class="text-gray-600 font-semibold text-lg print:text-black">{{ $match->kontingen_merah ?? 'Asal Kontingen' }}</div>
            </div>
            <div class="w-16 h-16 bg-gradient-to-bl from-red-600 to-red-800 rounded-lg shadow-md print:bg-[#df0000]"></div>
        </div>
    </div>

    <!-- SCORE TABLES -->
    @for($round = 1; $round <= 3; $round++)
    <div class="mb-6 rounded-lg overflow-hidden shadow-sm border border-gray-200 print:shadow-none print:border-none print:mb-2">
        <table class="w-full border-collapse text-center print:border-2 print:border-black bg-white">
            <thead>
                <tr>
                    <th colspan="4" class="bg-gradient-to-r from-blue-700 to-blue-500 text-white font-black text-lg tracking-widest py-2 print:bg-[#0000d0] print:border-2 print:border-black">BLUE CORNER</th>
                    <th rowspan="2" class="font-extrabold border-x border-gray-200 bg-gray-50 w-[6%] text-sm py-2 print:border-2 print:border-black text-gray-700 print:text-black">ROUND</th>
                    <th colspan="4" class="bg-gradient-to-l from-red-700 to-red-500 text-white font-black text-lg tracking-widest py-2 print:bg-[#df0000] print:border-2 print:border-black">RED CORNER</th>
                </tr>
                <tr class="font-bold bg-gray-50 text-gray-600 print:bg-white print:text-black text-xs uppercase tracking-wider">
                    <th class="w-[5%] py-2 border-b border-r border-gray-200 print:border print:border-black">TOTAL</th>
                    <th colspan="3" class="py-2 border-b border-gray-200 print:border print:border-black">DETAIL SCORE</th>
                    <th colspan="3" class="py-2 border-b border-gray-200 print:border print:border-black">DETAIL SCORE</th>
                    <th class="w-[5%] py-2 border-b border-l border-gray-200 print:border print:border-black">TOTAL</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @php
                    $labelClass = "w-[12%] font-semibold border-b border-x border-gray-200 text-gray-500 print:text-black print:border-black p-1 h-[40px]";
                    $wideClass = "w-[15%] border-b border-gray-200 print:border-black p-1 h-[40px] text-left align-middle bg-gray-50/30";
                    $narrowClass = "w-[5%] border-b border-gray-200 print:border-black p-1 h-[40px]";
                    $totalClass = "font-black text-2xl border-r border-gray-200 print:border-2 print:border-black p-1 h-[40px] text-blue-600 print:text-black bg-blue-50/30 print:bg-transparent";
                    $totalRightClass = "font-black text-2xl border-l border-gray-200 print:border-2 print:border-black p-1 h-[40px] text-red-600 print:text-black bg-red-50/30 print:bg-transparent";
                    $blueRoundScore = $awardsTotals['blue'][$round]['punch'] + $awardsTotals['blue'][$round]['kick'];
                    $redRoundScore = $awardsTotals['red'][$round]['punch'] + $awardsTotals['red'][$round]['kick'];
                @endphp
            <!-- Juri 1 -->
            <tr class="hover:bg-gray-50 transition-colors">
                <td rowspan="7" class="{{ $totalClass }}">{{ $blueRoundScore }}</td> <!-- Total Blue -->
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_1'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">JURI 1</td>
                <td rowspan="7" class="font-black border-x border-gray-200 bg-gray-50 print:border-2 print:border-black w-[6%] text-3xl h-[40px] text-gray-800 print:text-black">{{ $round }}</td> <!-- Round Number -->
                <td class="{{ $labelClass }}">JURI 1</td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_1'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
                <td rowspan="7" class="{{ $totalRightClass }}">{{ $redRoundScore }}</td> <!-- Total Red -->
            </tr>
            <!-- Juri 2 -->
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_2'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">JURI 2</td>
                <td class="{{ $labelClass }}">JURI 2</td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_2'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- Juri 3 -->
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_3'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">JURI 3</td>
                <td class="{{ $labelClass }}">JURI 3</td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_3'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- SCORE -->
            <tr class="bg-indigo-50/30 hover:bg-indigo-50 print:bg-transparent transition-colors">
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderAwards($awardHistory[$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }} text-indigo-700 print:text-black font-bold">SCORE</td>
                <td class="{{ $labelClass }} text-indigo-700 print:text-black font-bold">SCORE</td>
                <td class="{{ $wideClass }}">{!! renderAwards($awardHistory[$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- JATUHAN -->
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }} font-bold text-gray-700">{{ $round == 3 ? ($skor->jatuhan_biru ?? 0) : '-' }}</td>
                <td class="{{ $labelClass }}">JATUHAN</td>
                <td class="{{ $labelClass }}">JATUHAN</td>
                <td class="{{ $wideClass }} font-bold text-gray-700">{{ $round == 3 ? ($skor->jatuhan_merah ?? 0) : '-' }}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- HUKUMAN -->
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }} font-bold text-gray-700">{{ $round == 3 ? (($skor->teguran_biru ?? 0) + ($skor->peringatan_biru ?? 0)) : '-' }}</td>
                <td class="{{ $labelClass }}">HUKUMAN</td>
                <td class="{{ $labelClass }}">HUKUMAN</td>
                <td class="{{ $wideClass }} font-bold text-gray-700">{{ $round == 3 ? (($skor->teguran_merah ?? 0) + ($skor->peringatan_merah ?? 0)) : '-' }}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- BINAAN -->
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }} font-bold text-gray-700">{{ $round == 3 ? ($skor->binaan_biru ?? 0) : '-' }}</td>
                <td class="{{ $labelClass }}">BINAAN</td>
                <td class="{{ $labelClass }}">BINAAN</td>
                <td class="{{ $wideClass }} font-bold text-gray-700">{{ $round == 3 ? ($skor->binaan_merah ?? 0) : '-' }}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
        </tbody>
        </table>
    </div>
    @endfor

    <!-- DETAIL WINNER TABLE -->
    <div class="mt-8 rounded-lg overflow-hidden shadow-sm border border-gray-200 print:shadow-none print:border-none">
        <table class="w-full border-collapse bg-white font-bold text-left print:border-2 print:border-black">
            <thead>
                <tr>
                    <th colspan="2" class="bg-gray-800 text-white py-3 text-left pl-10 tracking-widest text-lg print:bg-black print:border-2 print:border-black">DETAIL WINNER</th>
                    <th colspan="8" class="bg-gray-800 text-white py-3 text-left pl-[10%] tracking-widest text-lg border-l border-gray-700 print:bg-black print:border-2 print:border-black">DETAIL WINNER</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 print:text-black">
                @php
                    $wtClass = "border-b border-gray-200 print:border print:border-black px-4 py-2 text-[0.95rem]";
                @endphp
                <tr>
                    <td class="w-[12%] {{ $wtClass }} text-gray-500">Nama</td>
                    <td class="w-[15%] {{ $wtClass }}">{{ strtolower($match->winner_corner) == 'biru' ? $match->sudut_biru : (strtolower($match->winner_corner) == 'merah' ? $match->sudut_merah : '-') }}</td>
                    <td rowspan="5" class="border-b border-gray-200 bg-gradient-to-br from-blue-600 to-blue-800 print:bg-[#0000d0] text-white text-[5rem] text-center font-black w-[12%] leading-none align-middle pb-[1rem] shadow-inner print:shadow-none print:border print:border-black">{{ $skor->skor_biru ?? 0 }}</td>
                    <td rowspan="5" class="{{ $wtClass }} text-center w-[3%] text-gray-400 italic">vs</td>
                    <td rowspan="5" class="border-b border-gray-200 bg-gradient-to-bl from-red-600 to-red-800 print:bg-[#df0000] text-white text-[5rem] text-center font-black w-[12%] leading-none align-middle pb-[1rem] shadow-inner print:shadow-none print:border print:border-black">{{ $skor->skor_merah ?? 0 }}</td>
                    <td class="w-[15%] {{ $wtClass }} border-l border-gray-200"></td>
                    <td class="w-[7%] {{ $wtClass }}"></td>
                    <td class="w-[7%] {{ $wtClass }}"></td>
                    <td class="w-[7%] {{ $wtClass }}"></td>
                    <td class="w-[7%] {{ $wtClass }}"></td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="{{ $wtClass }} text-gray-500">Kontingen</td>
                    <td class="{{ $wtClass }}">{{ strtolower($match->winner_corner) == 'biru' ? $match->kontingen_biru : (strtolower($match->winner_corner) == 'merah' ? $match->kontingen_merah : '-') }}</td>
                    <td class="{{ $wtClass }} border-l border-gray-200"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="{{ $wtClass }} text-gray-500">Kelas</td>
                    <td class="{{ $wtClass }}">{{ $match->kelas }} {{ strtoupper($match->golongan) }}</td>
                    <td class="{{ $wtClass }} border-l border-gray-200"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="{{ $wtClass }} text-gray-500">Sudut</td>
                    <td class="{{ $wtClass }}">{{ strtoupper($match->winner_corner ?? '-') }}</td>
                    <td class="{{ $wtClass }} border-l border-gray-200"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="{{ $wtClass }} text-gray-500">Winning By</td>
                    <td class="{{ $wtClass }}">{{ $match->winning_method ?? '-' }}</td>
                    <td class="{{ $wtClass }} border-l border-gray-200"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="{{ $wtClass }} text-gray-500">Time Stemp</td>
                    <td class="{{ $wtClass }} text-gray-500 font-medium">{{ $match->updated_at }}</td>
                    <td class="{{ $wtClass }} border-l border-gray-200"></td>
                    <td class="{{ $wtClass }}"></td>
                    <td class="{{ $wtClass }}"></td>
                <td class="{{ $wtClass }} bg-white align-top print:p-0">
                    <div class="flex flex-col justify-between h-[130px] pt-4 pb-2 print:h-[100px] print:block print:relative">
                        <div class="font-extrabold text-gray-800 text-[13px] tracking-wide text-center print:text-black leading-snug">Ketua<br>Pertandingan</div>
                        <div class="w-full text-center mt-12 print:mt-0 print:absolute print:bottom-2">
                            <span class="text-[13px] font-bold text-gray-800 print:text-black whitespace-nowrap">( <span class="capitalize print:uppercase">{{ $namaPetugas['ketua'] }}</span> )</span>
                        </div>
                    </div>
                </td>
                <td class="{{ $wtClass }} bg-white align-top print:p-0">
                    <div class="flex flex-col justify-between h-[130px] pt-4 pb-2 print:h-[100px] print:block print:relative">
                        <div class="font-extrabold text-gray-800 text-[13px] tracking-wide text-center print:text-black leading-snug whitespace-nowrap">Dewan Wasit<br>Juri</div>
                        <div class="w-full text-center mt-12 print:mt-0 print:absolute print:bottom-2">
                            <span class="text-[13px] font-bold text-gray-800 print:text-black whitespace-nowrap">( <span class="capitalize print:uppercase">{{ $namaPetugas['dewan'] }}</span> )</span>
                        </div>
                    </div>
                </td>
                <td class="{{ $wtClass }} bg-white align-top print:p-0">
                    <div class="flex flex-col justify-between h-[130px] pt-4 pb-2 print:h-[100px] print:block print:relative">
                        <div class="font-extrabold text-gray-800 text-[13px] tracking-wide text-center print:text-black leading-snug">Juri<br>1</div>
                        <div class="w-full text-center mt-12 print:mt-0 print:absolute print:bottom-2">
                            <span class="text-[13px] font-bold text-gray-800 print:text-black whitespace-nowrap">( <span class="capitalize print:uppercase">{{ $namaPetugas['juri_1'] }}</span> )</span>
                        </div>
                    </div>
                </td>
                <td class="{{ $wtClass }} bg-white align-top print:p-0">
                    <div class="flex flex-col justify-between h-[130px] pt-4 pb-2 print:h-[100px] print:block print:relative">
                        <div class="font-extrabold text-gray-800 text-[13px] tracking-wide text-center print:text-black leading-snug">Juri<br>2</div>
                        <div class="w-full text-center mt-12 print:mt-0 print:absolute print:bottom-2">
                            <span class="text-[13px] font-bold text-gray-800 print:text-black whitespace-nowrap">( <span class="capitalize print:uppercase">{{ $namaPetugas['juri_2'] }}</span> )</span>
                        </div>
                    </div>
                </td>
                <td class="{{ $wtClass }} bg-white align-top print:p-0">
                    <div class="flex flex-col justify-between h-[130px] pt-4 pb-2 print:h-[100px] print:block print:relative">
                        <div class="font-extrabold text-gray-800 text-[13px] tracking-wide text-center print:text-black leading-snug">Juri<br>3</div>
                        <div class="w-full text-center mt-12 print:mt-0 print:absolute print:bottom-2">
                            <span class="text-[13px] font-bold text-gray-800 print:text-black whitespace-nowrap">( <span class="capitalize print:uppercase">{{ $namaPetugas['juri_3'] }}</span> )</span>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
    
</div>
@endsection
