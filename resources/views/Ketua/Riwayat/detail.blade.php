@extends('Operator.layout.fullscreen')

@section('title', 'Detail Riwayat Pertandingan')

@section('content')
@php
function getAssignedBoxColor($key, $athlete, $sah) {
    if (!$sah) return 'bg-gray-100 text-gray-400 line-through border border-dashed border-gray-300';
    if (!$key) return $athlete == 'blue' ? 'bg-[#0000cc] text-white' : 'bg-[#cc0000] text-white';
    $key = (string) $key;
    $colors = [
        'bg-[#e74c3c] text-white',
        'bg-[#2ecc71] text-white',
        'bg-[#3498db] text-white',
        'bg-[#f39c12] text-black',
        'bg-[#9b59b6] text-white',
        'bg-[#1abc9c] text-white',
        'bg-[#e91e63] text-white',
        'bg-[#ff6f00] text-white',
        'bg-[#00bcd4] text-white',
        'bg-[#8bc34a] text-black',
    ];
    $hash = 0;
    for ($i = 0; $i < strlen($key); $i++) $hash = ord($key[$i]) + (($hash << 5) - $hash);
    return $colors[abs($hash) % count($colors)];
}

function renderBoxList($items, $athlete, $isScoreRow = false) {
    if (empty($items)) return '';
    $html = '<div class="flex flex-wrap items-center justify-center gap-1 p-0.5">';
    foreach ($items as $idx => $item) {
        $sah = $item['sah'] ?? true;
        $colorKey = $item['window_id'] ?? ($item['award_id'] ?? null);
        $boxClass = getAssignedBoxColor($colorKey, $athlete, $sah);
        $inputNum = $item['input_index'] ?? ($idx + 1);
        $typeLabel = $isScoreRow ? "Skor ke-{$inputNum}" : "Input ke-{$inputNum}";
        
        if (isset($item['pair_info']) && $item['pair_info']) {
            $pairInfo = $item['pair_info'];
            $tooltipText = "{$typeLabel} • " . $pairInfo['pair_label'];
        } elseif ($sah) {
            $tooltipText = "{$typeLabel} (Sah)";
        } else {
            $tooltipText = "{$typeLabel} • Tidak Sah (Tidak Berpasangan)";
        }
        
        $html .= '<div class="relative group inline-block">';
        $html .= '<span title="' . htmlspecialchars($tooltipText, ENT_QUOTES) . '" class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded shadow-sm cursor-pointer transition-transform group-hover:scale-110 ' . $boxClass . '">' . $item['value'] . '</span>';
        $html .= '<div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1.5 hidden group-hover:flex flex-col items-center z-50 pointer-events-none">';
        $html .= '<span class="bg-gray-900 text-white text-[10px] font-bold py-0.5 px-2 rounded shadow-lg whitespace-nowrap">' . htmlspecialchars($tooltipText, ENT_QUOTES) . '</span>';
        $html .= '<div class="w-1.5 h-1.5 bg-gray-900 rotate-45 -mt-1"></div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}
@endphp

<!-- HEADER TOPBAR -->
<header class="bg-white h-[70px] flex items-center justify-between px-6 shadow border-b border-gray-300 print:hidden">
    <div class="flex items-center">
        <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-11 h-11 object-contain mr-4" onerror="this.style.display='none'">
        <h1 class="text-[20px] font-extrabold text-black tracking-wide">
            MATCH - PARTAI {{ str_pad($match->partai ?? 0, 3, '0', STR_PAD_LEFT) }}
        </h1>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('ketua.riwayat') }}" class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded font-semibold transition shadow-sm text-xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
        <a href="{{ route('ketua.riwayat.export-pdf', $match->id) }}" target="_blank" class="flex items-center gap-2 px-5 py-2 bg-[#ffcc00] hover:bg-yellow-500 text-white rounded font-bold transition shadow-sm text-xs uppercase tracking-wider">
            <i class="fa-solid fa-print"></i>
            <span>Print PDF</span>
        </a>
    </div>
</header>

<style>
    .monitor-table th, .monitor-table td {
        border: 2px solid black;
        padding: 2px 4px;
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        color: black;
    }
    @media (max-width: 1024px) {
        .monitor-table th, .monitor-table td {
            font-size: 10px;
            padding: 1px 2px;
            border-width: 1.5px;
        }
    }
    .monitor-table {
        border-collapse: collapse;
        width: 100%;
    }
    .thick-border-bottom td {
        border-bottom: 3px solid black !important;
    }
    .main-border-wrap {
        border: 3px solid black;
    }
</style>

<div class="max-w-[1200px] mx-auto p-4 sm:p-6 bg-white my-6 border border-gray-200 rounded-xl shadow-md print:m-0 print:border-none print:shadow-none print:max-w-full">

    <!-- PESERTA HEADER CARD -->
    <div class="flex justify-between items-center mb-6 px-4 py-3 bg-gray-50/50 rounded-xl border border-gray-200">
        <!-- Sudut Biru (Kiri) -->
        <div class="flex items-center gap-4 w-1/3">
            <div class="w-12 h-7 border overflow-hidden shadow rounded-md shrink-0">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>
            <div class="w-10 h-10 rounded-full border-2 border-blue-700 bg-white flex items-center justify-center overflow-hidden shrink-0">
                <img src="{{ asset('images/icons/man.png') }}" class="w-6 h-6 object-contain">
            </div>
            <div>
                <p class="text-base sm:text-lg font-bold text-blue-800 leading-tight">{{ $match->sudut_biru ?? '-' }}</p>
                <p class="text-xs text-gray-500 font-medium">{{ $match->kontingen_biru ?? '-' }}</p>
            </div>
        </div>

        <!-- Info Partai -->
        <div class="text-center w-1/3">
            <p class="text-base font-extrabold text-black uppercase tracking-wider">Partai</p>
            <p class="text-2xl font-extrabold text-black leading-tight">{{ str_pad($match->partai ?? 0, 3, '0', STR_PAD_LEFT) }}</p>
            <p class="text-xs font-bold text-gray-600 uppercase mt-0.5">TANDING - {{ strtoupper($match->kelas ?? '-') }} ({{ strtoupper($match->golongan ?? '-') }})</p>
        </div>

        <!-- Sudut Merah (Kanan) -->
        <div class="flex items-center justify-end gap-4 w-1/3">
            <div class="text-right">
                <p class="text-base sm:text-lg font-bold text-red-700 leading-tight">{{ $match->sudut_merah ?? '-' }}</p>
                <p class="text-xs text-gray-500 font-medium">{{ $match->kontingen_merah ?? '-' }}</p>
            </div>
            <div class="w-10 h-10 rounded-full border-2 border-red-600 bg-white flex items-center justify-center overflow-hidden shrink-0">
                <img src="{{ asset('images/icons/man.png') }}" class="w-6 h-6 object-contain">
            </div>
            <div class="w-12 h-7 border overflow-hidden shadow rounded-md shrink-0">
                <div class="h-1/2 bg-red-600"></div>
                <div class="h-1/2 bg-white"></div>
            </div>
        </div>
    </div>

    <!-- SCORE TABLE MATRIX (FULL WIDTH - NO RIGHT SIDE PANEL FOR SCORE/PEMENANG/TIMER) -->
    <div class="main-border-wrap bg-white">
        <table class="monitor-table w-full">
            
            <!-- Header Utama -->
            <tr class="h-[32px]">
                <th colspan="4" class="bg-[#0000cc] text-white uppercase text-[15px] tracking-widest border-b-[3px] border-black">BLUE CORNER</th>
                <th rowspan="2" class="bg-white text-black text-xs font-extrabold px-2 w-[60px] border-b-[3px] border-black align-middle">ROUND</th>
                <th colspan="4" class="bg-[#cc0000] text-white uppercase text-[15px] tracking-widest border-b-[3px] border-black">RED CORNER</th>
            </tr>

            <!-- Sub Header -->
            <tr class="bg-white text-black text-xs uppercase h-[26px]">
                <th colspan="2" class="w-[90px] border-b-[3px] border-black">TOTAL</th>
                <th colspan="2" class="border-b-[3px] border-black">DETAIL SCORE</th>
                <th colspan="2" class="border-b-[3px] border-black">DETAIL SCORE</th>
                <th colspan="2" class="w-[90px] border-b-[3px] border-black">TOTAL</th>
            </tr>

            <!-- Ronde 1, 2, 3 Loop -->
            @php
                $details = ['JURI 1', 'JURI 2', 'JURI 3', 'SCORE', 'JATUHAN', 'HUKUMAN', 'BINAAN'];
                $detailKeys = ['juri_1', 'juri_2', 'juri_3', 'score', 'jatuhan', 'hukuman', 'binaan'];
            @endphp

            @for($r = 1; $r <= 3; $r++)
                @php
                    $blueAwardsRound = ($awardsTotals['blue'][$r]['punch'] ?? 0) + ($awardsTotals['blue'][$r]['kick'] ?? 0);
                    $redAwardsRound = ($awardsTotals['red'][$r]['punch'] ?? 0) + ($awardsTotals['red'][$r]['kick'] ?? 0);

                    $blueJatuhanPts = count($penaltiesPerRound[$r]['blue']['jatuhan'] ?? []) * 3;
                    $blueHukumanPts = 0;
                    foreach ($penaltiesPerRound[$r]['blue']['hukuman'] ?? [] as $h) {
                        $blueHukumanPts += abs((int)$h);
                    }
                    $blueRoundTotal = max(0, $blueAwardsRound + $blueJatuhanPts - $blueHukumanPts);

                    $redJatuhanPts = count($penaltiesPerRound[$r]['red']['jatuhan'] ?? []) * 3;
                    $redHukumanPts = 0;
                    foreach ($penaltiesPerRound[$r]['red']['hukuman'] ?? [] as $h) {
                        $redHukumanPts += abs((int)$h);
                    }
                    $redRoundTotal = max(0, $redAwardsRound + $redJatuhanPts - $redHukumanPts);
                @endphp

                @foreach($details as $index => $detail)
                    @php 
                        $isLast = ($index == count($details) - 1);
                        $rowClass = ($isLast && $r < 3) ? 'thick-border-bottom' : '';
                        $key = $detailKeys[$index];
                    @endphp

                    <tr class="{{ $rowClass }} h-[24px]">
                        
                        <!-- Total Kiri (Grand total, rowspan seluruh ronde) -->
                        @if($index == 0)
                            <td rowspan="{{ count($details) }}" class="w-[45px] align-middle p-1 border-r-2 border-black font-black text-xl {{ $r < 3 ? 'border-b-[4px]' : '' }}">
                                {{ $blueRoundTotal }}
                            </td>
                            <td rowspan="3" class="w-[45px] align-middle p-1 border-r-2 border-black font-bold text-sm">
                                {{ $blueAwardsRound }}
                            </td>
                        @elseif($index >= 3)
                            <td class="w-[45px] align-middle p-1 border-r-2 border-black">
                            </td>
                        @endif
                        
                        <!-- Detail Score Val Kiri -->
                        <td class="w-auto bg-white border-r-2 border-black">
                            @if($index < 3)
                                {!! renderBoxList($eventHistory[$key][$r]['blue'] ?? [], 'blue', false) !!}
                            @elseif($key === 'score')
                                {!! renderBoxList($awardHistory[$r]['blue'] ?? [], 'blue', true) !!}
                            @elseif($key === 'jatuhan')
                                {{ count($penaltiesPerRound[$r]['blue']['jatuhan'] ?? []) > 0 ? implode(', ', $penaltiesPerRound[$r]['blue']['jatuhan']) : '-' }}
                            @elseif($key === 'hukuman')
                                {{ count($penaltiesPerRound[$r]['blue']['hukuman'] ?? []) > 0 ? implode(', ', $penaltiesPerRound[$r]['blue']['hukuman']) : '-' }}
                            @elseif($key === 'binaan')
                                {{ count($penaltiesPerRound[$r]['blue']['binaan'] ?? []) > 0 ? implode(', ', $penaltiesPerRound[$r]['blue']['binaan']) : '-' }}
                            @endif
                        </td>
                        
                        <!-- Detail Score Text Kiri -->
                        <td class="uppercase text-right px-3 w-[120px] bg-white whitespace-nowrap border-l-2 border-black text-xs font-bold">{{ $detail }}</td>

                        <!-- Label Ronde -->
                        @if($index == 0)
                            <td rowspan="{{ count($details) }}" class="text-[20px] font-black bg-white align-middle border-x-[3px] border-black {{ $r < 3 ? 'border-b-[4px]' : '' }}">
                                {{ $r }}
                            </td>
                        @endif

                        <!-- Detail Score Text Kanan -->
                        <td class="uppercase text-left px-3 w-[120px] bg-white whitespace-nowrap border-r-2 border-black text-xs font-bold">{{ $detail }}</td>
                        
                        <!-- Detail Score Val Kanan -->
                        <td class="w-auto bg-white border-l-2 border-black">
                            @if($index < 3)
                                {!! renderBoxList($eventHistory[$key][$r]['red'] ?? [], 'red', false) !!}
                            @elseif($key === 'score')
                                {!! renderBoxList($awardHistory[$r]['red'] ?? [], 'red', true) !!}
                            @elseif($key === 'jatuhan')
                                {{ count($penaltiesPerRound[$r]['red']['jatuhan'] ?? []) > 0 ? implode(', ', $penaltiesPerRound[$r]['red']['jatuhan']) : '-' }}
                            @elseif($key === 'hukuman')
                                {{ count($penaltiesPerRound[$r]['red']['hukuman'] ?? []) > 0 ? implode(', ', $penaltiesPerRound[$r]['red']['hukuman']) : '-' }}
                            @elseif($key === 'binaan')
                                {{ count($penaltiesPerRound[$r]['red']['binaan'] ?? []) > 0 ? implode(', ', $penaltiesPerRound[$r]['red']['binaan']) : '-' }}
                            @endif
                        </td>

                        <!-- Total Kanan -->
                        @if($index == 0)
                            <td rowspan="3" class="w-[45px] align-middle p-1 border-l-2 border-black font-bold text-sm">
                                {{ $redAwardsRound }}
                            </td>
                            <td rowspan="{{ count($details) }}" class="w-[45px] align-middle p-1 border-l-2 border-black font-black text-xl {{ $r < 3 ? 'border-b-[4px]' : '' }}">
                                {{ $redRoundTotal }}
                            </td>
                        @elseif($index >= 3)
                            <td class="w-[45px] align-middle p-1 border-l-2 border-black">
                            </td>
                        @endif
                        
                    </tr>
                @endforeach
            @endfor

        </table>
    </div>

</div>

@endsection
