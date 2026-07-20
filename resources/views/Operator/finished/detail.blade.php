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
            $html .= '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold rounded-sm ' . getBoxColor($evt['window_id'], $athlete, true) . '">' . $evt['value'] . '</span>';
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
        <a href="{{ route('operator.tanding.finished.export-pdf', $match->id) }}" target="_blank" class="bg-[#ffcc00] hover:bg-yellow-500 text-white font-bold py-2 px-8 rounded text-sm tracking-widest shadow-md inline-block">
            PRINT
        </a>
    </div>
</div>

<div class="relative max-w-[1200px] mx-auto p-8 bg-white my-8 print:m-0 print:max-w-full print:p-0">

    <!-- PARTICIPANT INFO CARD -->
    <div class="flex justify-between items-start mb-4 bg-white print:bg-transparent print:border-none print:p-0 print:mb-4">
        <!-- Blue Corner Info -->
        <div class="flex items-start gap-3 w-1/3">
            <div class="w-12 h-12 bg-[#0000d0] print:bg-[#0000d0]"></div>
            <div class="leading-tight">
                <div class="text-[#0000d0] font-bold text-lg print:text-[#0000d0]">{{ $match->sudut_biru ?? 'Nama Atlet' }}</div>
                <div class="text-black font-bold text-xs print:text-black">{{ $match->kontingen_biru ?? 'Asal Kontingen' }}</div>
            </div>
        </div>

        <!-- Center Info -->
        <div class="text-center w-1/3 leading-tight pt-1">
            <div class="font-bold text-lg text-black tracking-wide">PARTAI {{ str_pad($match->partai, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="font-bold text-black mt-1 text-sm uppercase tracking-wider">TANDING - {{ $match->kelas }}</div>
            <div class="font-bold text-black mt-0.5 text-sm uppercase tracking-wider">{{ $match->golongan }}</div>
        </div>

        <!-- Red Corner Info -->
        <div class="flex items-start gap-3 justify-end w-1/3">
            <div class="text-right leading-tight">
                <div class="text-[#df0000] font-bold text-lg print:text-[#df0000]">{{ $match->sudut_merah ?? 'Nama Atlet' }}</div>
                <div class="text-black font-bold text-xs print:text-black">{{ $match->kontingen_merah ?? 'Asal Kontingen' }}</div>
            </div>
            <div class="w-12 h-12 bg-[#df0000] print:bg-[#df0000]"></div>
        </div>
    </div>

    <!-- SCORE TABLES -->
    @for($round = 1; $round <= 3; $round++)
    <div class="mb-2">
        <table class="w-full border-collapse text-center border-2 border-black bg-white">
            <thead>
                <tr>
                    <th colspan="4" class="bg-[#0000d0] text-white font-bold tracking-wide py-1 border border-black uppercase text-sm">BLUE CORNER</th>
                    <th rowspan="2" class="font-bold border border-black bg-white w-[6%] text-sm py-1 text-black uppercase">ROUND</th>
                    <th colspan="4" class="bg-[#df0000] text-white font-bold tracking-wide py-1 border border-black uppercase text-sm">RED CORNER</th>
                </tr>
                <tr class="font-bold bg-white text-black text-[10px] uppercase">
                    <th class="w-[5%] py-1 border border-black">TOTAL</th>
                    <th colspan="3" class="py-1 border border-black">DETAIL SCORE</th>
                    <th colspan="3" class="py-1 border border-black">DETAIL SCORE</th>
                    <th class="w-[5%] py-1 border border-black">TOTAL</th>
                </tr>
            </thead>
            <tbody class="text-xs">
                @php
                    $labelClass = "w-[12%] font-bold border border-black text-black p-1 h-[25px] text-[10px]";
                    $wideClass = "w-[15%] border border-black p-1 h-[25px] text-left align-middle bg-white";
                    $narrowClass = "w-[5%] border border-black p-1 h-[25px] bg-white";
                    $totalClass = "font-black text-xl border border-black p-1 text-black bg-white";
                    $blueRoundScore = $awardsTotals['blue'][$round]['punch'] + $awardsTotals['blue'][$round]['kick'];
                    $redRoundScore = $awardsTotals['red'][$round]['punch'] + $awardsTotals['red'][$round]['kick'];
                @endphp
            <!-- Juri 1 -->
            <tr>
                <td rowspan="7" class="{{ $totalClass }}">{{ $blueRoundScore }}</td> <!-- Total Blue -->
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_1'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">JURI 1</td>
                <td rowspan="7" class="font-black border border-black bg-white text-2xl text-black">{{ $round }}</td> <!-- Round Number -->
                <td class="{{ $labelClass }}">JURI 1</td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_1'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
                <td rowspan="7" class="{{ $totalClass }}">{{ $redRoundScore }}</td> <!-- Total Red -->
            </tr>
            <!-- Juri 2 -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_2'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">JURI 2</td>
                <td class="{{ $labelClass }}">JURI 2</td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_2'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- Juri 3 -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_3'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">JURI 3</td>
                <td class="{{ $labelClass }}">JURI 3</td>
                <td class="{{ $wideClass }}">{!! renderEvents($eventHistory['juri_3'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- SCORE -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }}">{!! renderAwards($awardHistory[$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="{{ $labelClass }}">SCORE</td>
                <td class="{{ $labelClass }}">SCORE</td>
                <td class="{{ $wideClass }}">{!! renderAwards($awardHistory[$round]['red'] ?? [], 'red') !!}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- JATUHAN -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }} font-bold text-black text-center">{{ $round == 3 ? ($skor->jatuhan_biru ?? 0) : '-' }}</td>
                <td class="{{ $labelClass }}">JATUHAN</td>
                <td class="{{ $labelClass }}">JATUHAN</td>
                <td class="{{ $wideClass }} font-bold text-black text-center">{{ $round == 3 ? ($skor->jatuhan_merah ?? 0) : '-' }}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- HUKUMAN -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }} font-bold text-black text-center">{{ $round == 3 ? (($skor->teguran_biru ?? 0) + ($skor->peringatan_biru ?? 0)) : '-' }}</td>
                <td class="{{ $labelClass }}">HUKUMAN</td>
                <td class="{{ $labelClass }}">HUKUMAN</td>
                <td class="{{ $wideClass }} font-bold text-black text-center">{{ $round == 3 ? (($skor->teguran_merah ?? 0) + ($skor->peringatan_merah ?? 0)) : '-' }}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
            <!-- BINAAN -->
            <tr>
                <td class="{{ $narrowClass }}"></td>
                <td class="{{ $wideClass }} font-bold text-black text-center">{{ $round == 3 ? ($skor->binaan_biru ?? 0) : '-' }}</td>
                <td class="{{ $labelClass }}">BINAAN</td>
                <td class="{{ $labelClass }}">BINAAN</td>
                <td class="{{ $wideClass }} font-bold text-black text-center">{{ $round == 3 ? ($skor->binaan_merah ?? 0) : '-' }}</td>
                <td class="{{ $narrowClass }}"></td>
            </tr>
        </tbody>
        </table>
    </div>
    @endfor

    <!-- DETAIL WINNER TABLE -->
    <div class="mt-4">
        <table class="w-full border-collapse bg-white font-medium text-left border-2 border-black">
            <thead>
                <tr>
                    <th colspan="2" class="bg-slate-900 text-white py-1 px-4 text-center font-bold text-sm border border-black">Detail Winner</th>
                    <th colspan="3" class="bg-slate-900 text-white py-1 px-4 text-center font-bold text-sm border border-black">Detail Score</th>
                    <th colspan="5" class="bg-slate-900 text-white py-1 px-4 text-center font-bold text-sm border border-black">Signature</th>
                </tr>
            </thead>
            <tbody class="text-black">
                @php
                    $wtClass = "border border-black px-4 py-1.5 text-sm";
                    $emptyBg = "bg-gray-100";
                @endphp
                <tr>
                    <td class="w-[10%] {{ $wtClass }} text-black font-bold">Nama</td>
                    <td class="w-[16%] {{ $wtClass }} uppercase">{{ strtolower($match->winner_corner) == 'biru' ? $match->sudut_biru : (strtolower($match->winner_corner) == 'merah' ? $match->sudut_merah : '-') }}</td>
                    <td colspan="3" class="border border-black {{ $emptyBg }}"></td>
                    <td rowspan="6" class="w-[10%] border border-black bg-white align-top relative">
                        <div class="pt-4 pb-8 h-full text-center">
                            <div class="font-bold text-black text-[13px] tracking-wide leading-snug">Ketua<br>Pertandingan</div>
                            <div class="absolute bottom-2 left-0 right-0 text-center">
                                <span class="text-[13px] font-bold text-black whitespace-nowrap uppercase">( {{ $namaPetugas['ketua'] }} )</span>
                            </div>
                        </div>
                    </td>
                    <td rowspan="6" class="w-[10%] border border-black bg-white align-top relative">
                        <div class="pt-4 pb-8 h-full text-center">
                            <div class="font-bold text-black text-[13px] tracking-wide leading-snug whitespace-nowrap">Dewan Wasit<br>Juri</div>
                            <div class="absolute bottom-2 left-0 right-0 text-center">
                                <span class="text-[13px] font-bold text-black whitespace-nowrap uppercase">( {{ $namaPetugas['dewan'] }} )</span>
                            </div>
                        </div>
                    </td>
                    <td rowspan="6" class="w-[10%] border border-black bg-white align-top relative">
                        <div class="pt-4 pb-8 h-full text-center">
                            <div class="font-bold text-black text-[13px] tracking-wide leading-snug">Juri<br>1</div>
                            <div class="absolute bottom-2 left-0 right-0 text-center">
                                <span class="text-[13px] font-bold text-black whitespace-nowrap uppercase">( {{ $namaPetugas['juri_1'] }} )</span>
                            </div>
                        </div>
                    </td>
                    <td rowspan="6" class="w-[10%] border border-black bg-white align-top relative">
                        <div class="pt-4 pb-8 h-full text-center">
                            <div class="font-bold text-black text-[13px] tracking-wide leading-snug">Juri<br>2</div>
                            <div class="absolute bottom-2 left-0 right-0 text-center">
                                <span class="text-[13px] font-bold text-black whitespace-nowrap uppercase">( {{ $namaPetugas['juri_2'] }} )</span>
                            </div>
                        </div>
                    </td>
                    <td rowspan="6" class="w-[10%] border border-black bg-white align-top relative">
                        <div class="pt-4 pb-8 h-full text-center">
                            <div class="font-bold text-black text-[13px] tracking-wide leading-snug">Juri<br>3</div>
                            <div class="absolute bottom-2 left-0 right-0 text-center">
                                <span class="text-[13px] font-bold text-black whitespace-nowrap uppercase">( {{ $namaPetugas['juri_3'] }} )</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="{{ $wtClass }} text-black font-bold">Kontingen</td>
                    <td class="{{ $wtClass }} uppercase">{{ strtolower($match->winner_corner) == 'biru' ? $match->kontingen_biru : (strtolower($match->winner_corner) == 'merah' ? $match->kontingen_merah : '-') }}</td>
                    <td rowspan="4" class="border border-black text-white text-5xl text-center font-bold w-[10%] align-middle bg-[#0000d0]">{{ $skor->skor_biru ?? 0 }}</td>
                    <td rowspan="4" class="{{ $wtClass }} text-center w-[4%] text-black font-bold bg-gray-50">vs</td>
                    <td rowspan="4" class="border border-black text-white text-5xl text-center font-bold w-[10%] align-middle bg-[#df0000]">{{ $skor->skor_merah ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="{{ $wtClass }} text-black font-bold">Kelas</td>
                    <td class="{{ $wtClass }} uppercase">{{ $match->kelas }} {{ strtoupper($match->golongan) }}</td>
                </tr>
                <tr>
                    <td class="{{ $wtClass }} text-black font-bold">Sudut</td>
                    <td class="{{ $wtClass }}">
                        <span class="inline-block px-2 py-0.5 text-xs font-bold text-white {{ strtolower($match->winner_corner) == 'biru' ? 'bg-blue-800' : (strtolower($match->winner_corner) == 'merah' ? 'bg-red-800' : 'bg-gray-500') }}">{{ strtoupper($match->winner_corner ?? '-') }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="{{ $wtClass }} text-black font-bold">Winning By</td>
                    <td class="{{ $wtClass }}">
                        <span class="inline-block px-2 py-0.5 text-xs font-bold text-white bg-slate-800 uppercase">{{ $match->winning_method ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="{{ $wtClass }} text-black font-bold">Time Stemp</td>
                    <td class="{{ $wtClass }}">{{ $match->updated_at }}</td>
                    <td colspan="3" class="border border-black {{ $emptyBg }}"></td>
                </tr>
            </tbody>
        </table>
    </div>
    
</div>
@endsection
