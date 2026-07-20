<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Score</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; box-sizing: border-box; }
        table { border-collapse: collapse; width: 100%; }
        
        /* HEADER INFO */
        .header-table { width: 100%; margin-bottom: 5px; }
        .header-table td { vertical-align: middle; }
        .box-blue { background-color: #0000d0; }
        .box-red { background-color: #df0000; }
        .name-blue { color: #0000d0; font-weight: bold; font-size: 18px; margin: 0; line-height: 1.1; }
        .name-red { color: #df0000; font-weight: bold; font-size: 18px; margin: 0; text-align: right; line-height: 1.1; }
        .kontingen-blue { font-weight: bold; font-size: 12px; margin: 0; color: black; line-height: 1.2; padding-top: 2px; }
        .kontingen-red { font-weight: bold; font-size: 12px; margin: 0; text-align: right; color: black; line-height: 1.2; padding-top: 2px; }
        .partai { font-size: 14px; font-weight: bold; margin: 0; letter-spacing: 2px; }
        .kelas { font-size: 12px; font-weight: bold; margin-top: 2px; }
        
        /* SCORE TABLES */
        .score-table { width: 100%; text-align: center; border: 2px solid black; margin-bottom: 6px; }
        .score-table th, .score-table td { border: 1px solid black; padding: 1px 2px; height: 21px; }
        .score-table .bg-blue { background-color: #0000d0; color: white; font-weight: bold; letter-spacing: 2px; font-size: 13px; }
        .score-table .bg-red { background-color: #df0000; color: white; font-weight: bold; letter-spacing: 2px; font-size: 13px; }
        .score-table .label { font-weight: bold; width: 12%; font-size: 10px; }
        .score-table .narrow { width: 3%; }
        .score-table .wide { width: 22%; text-align: left; padding-left: 5px; font-size: 10px; }
        .score-table .total-hdr { width: 8%; font-size: 10px; }
        .score-table .round-col { font-weight: bold; font-size: 16px; border-left: 2px solid black; border-right: 2px solid black; width: 6%; }
        .score-table .total-col { font-weight: bold; font-size: 18px; }
        
        /* WINNER TABLE */
        .winner-table { width: 100%; border: 2px solid black; margin-top: 15px; text-align: left; font-weight: bold; }
        .winner-table th { background-color: #1a202c; color: white; border: 1px solid black; padding: 3px 6px; font-size: 12px; letter-spacing: 1px; }
        .winner-table td { border: 1px solid black; padding: 3px 6px; font-size: 10px; }
        .winner-table .th-center { text-align: center; }
        .winner-table .bg-gray { background-color: #f3f4f6; }
        .winner-table .bg-light { background-color: #f9fafb; }
        .winner-table .grad-blue { background-color: #0000d0; color: white; font-size: 40px; font-weight: 900; text-align: center; vertical-align: middle; }
        .winner-table .grad-red { background-color: #df0000; color: white; font-size: 40px; font-weight: 900; text-align: center; vertical-align: middle; }
        .box-sudut { display: inline-block; padding: 2px 4px; color: white; font-size: 9px; }
        .box-sudut.biru { background-color: #1e40af; }
        .box-sudut.merah { background-color: #991b1b; }
        .box-sudut.abu { background-color: #6b7280; }
        .box-winning { display: inline-block; padding: 2px 4px; color: white; font-size: 9px; background-color: #1f2937; text-transform: uppercase; }
        .sig-label { background-color: #f9fafb; text-align: center; font-size: 9px; padding: 2px; }
        
        .w-12 { width: 10%; } .w-15 { width: 16%; } .w-3 { width: 4%; text-align: center; } .w-10 { width: 10%; }
    </style>
</head>
<body>

    <table class="header-table" style="border: none;">
        <tr>
            <td style="width: 35%; border: none;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 45px; border: none; padding: 0;">
                            <div class="box-blue" style="margin: 0; width: 45px; height: 45px;"></div>
                        </td>
                        <td style="border: none; padding: 0 0 0 10px; text-align: left; vertical-align: middle;">
                            <p class="name-blue">{{ $match->sudut_biru ?? 'Nama Atlet' }}</p>
                            <p class="kontingen-blue">{{ $match->kontingen_biru ?? 'Asal Kontingen' }}</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 30%; text-align: center; border: none;">
                <p class="partai">PARTAI {{ str_pad($match->partai, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="kelas">TANDING - {{ $match->kelas }}</p>
                <p class="kelas">{{ strtoupper($match->golongan) }}</p>
            </td>
            <td style="width: 35%; border: none;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; padding: 0 10px 0 0; text-align: right; vertical-align: middle;">
                            <p class="name-red">{{ $match->sudut_merah ?? 'Nama Atlet' }}</p>
                            <p class="kontingen-red">{{ $match->kontingen_merah ?? 'Asal Kontingen' }}</p>
                        </td>
                        <td style="width: 45px; border: none; padding: 0;">
                            <div class="box-red" style="margin: 0; width: 45px; height: 45px;"></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @php
        if (!function_exists('getPdfBoxColor')) {
            function getPdfBoxColor($awardId, $athlete, $sah) {
                if (!$sah) return '';
                if (!$awardId) return $athlete == 'blue' ? 'background-color:#0000d0;color:white;' : 'background-color:#df0000;color:white;';
                $colors = ['background-color:#ff3b8f;color:white;', 'background-color:#ffcc00;color:black;', 'background-color:#2d2d2d;color:white;', 'background-color:#8b3dff;color:white;', 'background-color:#10b981;color:white;', 'background-color:#f97316;color:white;', 'background-color:#0ea5e9;color:white;', 'background-color:#eab308;color:white;', 'background-color:#ec4899;color:white;', 'background-color:#84cc16;color:white;'];
                $hash = 0;
                for ($i = 0; $i < strlen($awardId); $i++) $hash = ord($awardId[$i]) + (($hash << 5) - $hash);
                return $colors[abs($hash) % count($colors)];
            }
        }
        if (!function_exists('renderPdfEvents')) {
            function renderPdfEvents($events, $athlete) {
                if (empty($events)) return '';
                $html = '<div style="text-align: left;">';
                foreach ($events as $evt) {
                    if ($evt['sah']) {
                        $html .= '<span style="display:inline-block; min-width:14px; text-align:center; padding:1px 2px; font-weight:bold; font-size:10px; border-radius:2px; margin-right:2px; ' . getPdfBoxColor($evt['window_id'], $athlete, true) . '">' . $evt['value'] . '</span>';
                    } else {
                        $html .= '<span style="display:inline-block; min-width:14px; text-align:center; padding:1px 2px; font-weight:bold; font-size:10px; color:#666; text-decoration:line-through; margin-right:2px;">' . $evt['value'] . '</span>';
                    }
                }
                $html .= '</div>';
                return $html;
            }
        }
        if (!function_exists('renderPdfAwards')) {
            function renderPdfAwards($awards, $athlete) {
                if (empty($awards)) return '';
                $html = '<div style="text-align: left;">';
                foreach ($awards as $awd) {
                    $html .= '<span style="display:inline-block; min-width:14px; text-align:center; padding:1px 2px; font-weight:bold; font-size:10px; border-radius:2px; margin-right:2px; ' . getPdfBoxColor($awd['award_id'], $athlete, true) . '">' . $awd['value'] . '</span>';
                }
                $html .= '</div>';
                return $html;
            }
        }
    @endphp

    @for($round = 1; $round <= 3; $round++)
    @php
        $blueRoundScore = $awardsTotals['blue'][$round]['punch'] + $awardsTotals['blue'][$round]['kick'];
        $redRoundScore = $awardsTotals['red'][$round]['punch'] + $awardsTotals['red'][$round]['kick'];
    @endphp
    <table class="score-table">
        <thead>
            <tr>
                <th colspan="4" class="bg-blue">BLUE CORNER</th>
                <th rowspan="2" class="round-col">ROUND</th>
                <th colspan="4" class="bg-red">RED CORNER</th>
            </tr>
            <tr>
                <th class="total-hdr">TOTAL</th>
                <th colspan="3">DETAIL SCORE</th>
                <th colspan="3">DETAIL SCORE</th>
                <th class="total-hdr">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="7" class="total-col">{{ $blueRoundScore }}</td>
                <td class="narrow"></td>
                <td class="wide">{!! renderPdfEvents($eventHistory['juri_1'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="label">JURI 1</td>
                <td rowspan="7" class="round-col" style="font-size: 22px;">{{ $round }}</td>
                <td class="label">JURI 1</td>
                <td class="wide">{!! renderPdfEvents($eventHistory['juri_1'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="narrow"></td>
                <td rowspan="7" class="total-col">{{ $redRoundScore }}</td>
            </tr>
            <tr>
                <td class="narrow"></td>
                <td class="wide">{!! renderPdfEvents($eventHistory['juri_2'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="label">JURI 2</td>
                <td class="label">JURI 2</td>
                <td class="wide">{!! renderPdfEvents($eventHistory['juri_2'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="narrow"></td>
            </tr>
            <tr>
                <td class="narrow"></td>
                <td class="wide">{!! renderPdfEvents($eventHistory['juri_3'][$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="label">JURI 3</td>
                <td class="label">JURI 3</td>
                <td class="wide">{!! renderPdfEvents($eventHistory['juri_3'][$round]['red'] ?? [], 'red') !!}</td>
                <td class="narrow"></td>
            </tr>
            <tr>
                <td class="narrow"></td>
                <td class="wide">{!! renderPdfAwards($awardHistory[$round]['blue'] ?? [], 'blue') !!}</td>
                <td class="label">SCORE</td>
                <td class="label">SCORE</td>
                <td class="wide">{!! renderPdfAwards($awardHistory[$round]['red'] ?? [], 'red') !!}</td>
                <td class="narrow"></td>
            </tr>
            <tr>
                <td class="narrow"></td>
                <td class="wide">{{ $round == 3 ? ($skor->jatuhan_biru ?? 0) : '-' }}</td>
                <td class="label">JATUHAN</td>
                <td class="label">JATUHAN</td>
                <td class="wide">{{ $round == 3 ? ($skor->jatuhan_merah ?? 0) : '-' }}</td>
                <td class="narrow"></td>
            </tr>
            <tr>
                <td class="narrow"></td>
                <td class="wide">{{ $round == 3 ? (($skor->teguran_biru ?? 0) + ($skor->peringatan_biru ?? 0)) : '-' }}</td>
                <td class="label">HUKUMAN</td>
                <td class="label">HUKUMAN</td>
                <td class="wide">{{ $round == 3 ? (($skor->teguran_merah ?? 0) + ($skor->peringatan_merah ?? 0)) : '-' }}</td>
                <td class="narrow"></td>
            </tr>
            <tr>
                <td class="narrow"></td>
                <td class="wide">{{ $round == 3 ? ($skor->binaan_biru ?? 0) : '-' }}</td>
                <td class="label">BINAAN</td>
                <td class="label">BINAAN</td>
                <td class="wide">{{ $round == 3 ? ($skor->binaan_merah ?? 0) : '-' }}</td>
                <td class="narrow"></td>
            </tr>
        </tbody>
    </table>
    @endfor

    <table class="winner-table">
        <thead>
            <tr>
                <th colspan="2" class="th-center">Detail Winner</th>
                <th colspan="3" class="th-center">Detail Score</th>
                <th colspan="5" class="th-center">Signature</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="w-12">Nama</td>
                <td class="w-15" style="text-transform: uppercase;">{{ strtolower($match->winner_corner) == 'biru' ? $match->sudut_biru : (strtolower($match->winner_corner) == 'merah' ? $match->sudut_merah : '-') }}</td>
                <td colspan="3" class="bg-gray"></td>
                <td rowspan="5" style="text-align: center; vertical-align: top; padding-top: 10px; border-bottom: none;">
                    Ketua<br>Pertandingan
                </td>
                <td rowspan="5" style="text-align: center; vertical-align: top; padding-top: 10px; border-bottom: none;">
                    Dewan Wasit<br>Juri
                </td>
                <td rowspan="5" style="text-align: center; vertical-align: top; padding-top: 10px; border-bottom: none;">
                    Juri<br>1
                </td>
                <td rowspan="5" style="text-align: center; vertical-align: top; padding-top: 10px; border-bottom: none;">
                    Juri<br>2
                </td>
                <td rowspan="5" style="text-align: center; vertical-align: top; padding-top: 10px; border-bottom: none;">
                    Juri<br>3
                </td>
            </tr>
            <tr>
                <td>Kontingen</td>
                <td style="text-transform: uppercase;">{{ strtolower($match->winner_corner) == 'biru' ? $match->kontingen_biru : (strtolower($match->winner_corner) == 'merah' ? $match->kontingen_merah : '-') }}</td>
                <td rowspan="4" class="w-12 grad-blue">{{ $skor->skor_biru ?? 0 }}</td>
                <td rowspan="4" class="w-3 bg-light th-center">vs</td>
                <td rowspan="4" class="w-12 grad-red">{{ $skor->skor_merah ?? 0 }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td style="text-transform: uppercase;">{{ $match->kelas }} {{ strtoupper($match->golongan) }}</td>
            </tr>
            <tr>
                <td>Sudut</td>
                <td>
                    @php $winCornerClass = strtolower($match->winner_corner) == 'biru' ? 'biru' : (strtolower($match->winner_corner) == 'merah' ? 'merah' : 'abu'); @endphp
                    <span class="box-sudut {{ $winCornerClass }}">{{ strtoupper($match->winner_corner ?? '-') }}</span>
                </td>
            </tr>
            <tr>
                <td>Winning By</td>
                <td>
                    <span class="box-winning">{{ $match->winning_method ?? '-' }}</span>
                </td>
            </tr>
            <tr>
                <td>Time Stemp</td>
                <td>{{ $match->updated_at }}</td>
                <td colspan="3" class="bg-gray"></td>
                <td style="text-align: center; vertical-align: bottom; padding-bottom: 10px; border-top: none;">
                    <span style="font-size: 10px; font-weight: bold; text-transform: uppercase; white-space: nowrap;">( {{ $namaPetugas['ketua'] }} )</span>
                </td>
                <td style="text-align: center; vertical-align: bottom; padding-bottom: 10px; border-top: none;">
                    <span style="font-size: 10px; font-weight: bold; text-transform: uppercase; white-space: nowrap;">( {{ $namaPetugas['dewan'] }} )</span>
                </td>
                <td style="text-align: center; vertical-align: bottom; padding-bottom: 10px; border-top: none;">
                    <span style="font-size: 10px; font-weight: bold; text-transform: uppercase; white-space: nowrap;">( {{ $namaPetugas['juri_1'] }} )</span>
                </td>
                <td style="text-align: center; vertical-align: bottom; padding-bottom: 10px; border-top: none;">
                    <span style="font-size: 10px; font-weight: bold; text-transform: uppercase; white-space: nowrap;">( {{ $namaPetugas['juri_2'] }} )</span>
                </td>
                <td style="text-align: center; vertical-align: bottom; padding-bottom: 10px; border-top: none;">
                    <span style="font-size: 10px; font-weight: bold; text-transform: uppercase; white-space: nowrap;">( {{ $namaPetugas['juri_3'] }} )</span>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
