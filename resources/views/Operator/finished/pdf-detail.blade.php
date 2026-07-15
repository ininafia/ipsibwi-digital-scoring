<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Score</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; }
        
        /* HEADER INFO */
        .header-table { width: 100%; margin-bottom: 15px; }
        .header-table td { vertical-align: top; }
        .box-blue { width: 45px; height: 45px; background-color: #0000d0; float: left; margin-right: 15px; border-radius: 3px; }
        .box-red { width: 45px; height: 45px; background-color: #df0000; float: right; margin-left: 15px; border-radius: 3px; }
        .name-blue { color: #0000d0; font-weight: bold; font-size: 18px; margin: 0; }
        .name-red { color: #df0000; font-weight: bold; font-size: 18px; margin: 0; text-align: right; }
        .kontingen-blue { font-weight: bold; font-size: 14px; margin: 0; }
        .kontingen-red { font-weight: bold; font-size: 14px; margin: 0; text-align: right; }
        .partai { font-size: 18px; font-weight: bold; margin: 0; letter-spacing: 2px; }
        .kelas { font-size: 16px; font-weight: bold; margin-top: 5px; }
        
        /* SCORE TABLES */
        .score-table { width: 100%; text-align: center; border: 2px solid black; margin-bottom: 5px; }
        .score-table th, .score-table td { border: 1px solid black; padding: 2px; height: 25px; }
        .score-table .bg-blue { background-color: #0000d0; color: white; font-weight: bold; letter-spacing: 2px; font-size: 14px; }
        .score-table .bg-red { background-color: #df0000; color: white; font-weight: bold; letter-spacing: 2px; font-size: 14px; }
        .score-table .border-heavy { border: 2px solid black; }
        .score-table .label { font-weight: bold; width: 12%; }
        .score-table .narrow { width: 5%; }
        .score-table .wide { width: 15%; }
        .score-table .round-col { font-weight: bold; font-size: 18px; border-left: 2px solid black; border-right: 2px solid black; width: 6%; }
        .score-table .total-col { font-weight: bold; font-size: 18px; border: 2px solid black; }
        
        /* WINNER TABLE */
        .winner-table { width: 100%; border: 2px solid black; margin-top: 15px; text-align: left; font-weight: bold; }
        .winner-table th { background-color: black; color: white; border: 2px solid black; padding: 5px 10px; font-size: 14px; letter-spacing: 2px; }
        .winner-table td { border: 1px solid black; padding: 4px 6px; font-size: 11px; }
        .big-blue { background-color: #0000d0; color: white; font-size: 45px; font-weight: 900; text-align: center; vertical-align: middle; }
        .big-red { background-color: #df0000; color: white; font-size: 45px; font-weight: 900; text-align: center; vertical-align: middle; }
        
        .w-12 { width: 12%; } .w-15 { width: 15%; } .w-3 { width: 3%; text-align: center; } .w-7 { width: 7%; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 35%;">
                <div class="box-blue"></div>
                <div style="float: left;">
                    <p class="name-blue">{{ $match->sudut_biru ?? 'Nama Atlet' }}</p>
                    <p class="kontingen-blue">{{ $match->kontingen_biru ?? 'Asal Kontingen' }}</p>
                </div>
            </td>
            <td style="width: 30%; text-align: center;">
                <p class="partai">PARTAI {{ str_pad($match->partai, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="kelas">TANDING - {{ $match->kelas }} {{ strtoupper($match->golongan) }}</p>
            </td>
            <td style="width: 35%;">
                <div class="box-red"></div>
                <div style="float: right;">
                    <p class="name-red">{{ $match->sudut_merah ?? 'Nama Atlet' }}</p>
                    <p class="kontingen-red">{{ $match->kontingen_merah ?? 'Asal Kontingen' }}</p>
                </div>
            </td>
        </tr>
    </table>

    @for($round = 1; $round <= 3; $round++)
    @php
        $blueRoundScore = $awardsTotals['blue'][$round]['punch'] + $awardsTotals['blue'][$round]['kick'];
        $redRoundScore = $awardsTotals['red'][$round]['punch'] + $awardsTotals['red'][$round]['kick'];
        
        function getPdfBoxColor($awardId, $athlete, $sah) {
            if (!$sah) return '';
            if (!$awardId) return $athlete == 'blue' ? 'background-color:#0000d0;color:white;' : 'background-color:#df0000;color:white;';
            $colors = ['background-color:#ff3b8f;color:white;', 'background-color:#ffcc00;color:black;', 'background-color:#2d2d2d;color:white;', 'background-color:#8b3dff;color:white;', 'background-color:#10b981;color:white;', 'background-color:#f97316;color:white;', 'background-color:#0ea5e9;color:white;', 'background-color:#eab308;color:white;', 'background-color:#ec4899;color:white;', 'background-color:#84cc16;color:white;'];
            $hash = 0;
            for ($i = 0; $i < strlen($awardId); $i++) $hash = ord($awardId[$i]) + (($hash << 5) - $hash);
            return $colors[abs($hash) % count($colors)];
        }
        function renderPdfEvents($events, $athlete) {
            if (empty($events)) return '';
            $html = '<div style="text-align: left;">';
            foreach ($events as $evt) {
                if ($evt['sah']) {
                    $html .= '<span style="display:inline-block; min-width:14px; text-align:center; padding:1px 2px; font-weight:bold; font-size:10px; border-radius:2px; margin-right:2px; ' . getPdfBoxColor($evt['award_id'], $athlete, true) . '">' . $evt['value'] . '</span>';
                } else {
                    $html .= '<span style="display:inline-block; min-width:14px; text-align:center; padding:1px 2px; font-weight:bold; font-size:10px; color:#666; text-decoration:line-through; margin-right:2px;">' . $evt['value'] . '</span>';
                }
            }
            $html .= '</div>';
            return $html;
        }
        function renderPdfAwards($awards, $athlete) {
            if (empty($awards)) return '';
            $html = '<div style="text-align: left;">';
            foreach ($awards as $awd) {
                $html .= '<span style="display:inline-block; min-width:14px; text-align:center; padding:1px 2px; font-weight:bold; font-size:10px; border-radius:2px; margin-right:2px; ' . getPdfBoxColor($awd['award_id'], $athlete, true) . '">' . $awd['value'] . '</span>';
            }
            $html .= '</div>';
            return $html;
        }
    @endphp
    <table class="score-table">
        <thead>
            <tr>
                <th colspan="4" class="bg-blue border-heavy">BLUE CORNER</th>
                <th rowspan="2" class="round-col">ROUND</th>
                <th colspan="4" class="bg-red border-heavy">RED CORNER</th>
            </tr>
            <tr>
                <th class="narrow border-heavy">TOTAL</th>
                <th colspan="3" class="border-heavy">DETAIL SCORE</th>
                <th colspan="3" class="border-heavy">DETAIL SCORE</th>
                <th class="narrow border-heavy">TOTAL</th>
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
                <th colspan="2" style="padding-left: 40px;">DETAIL WINNER</th>
                <th colspan="8" style="padding-left: 10%;">DETAIL WINNER</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="w-12">Nama</td>
                <td class="w-15">{{ strtolower($match->winner_corner) == 'biru' ? $match->sudut_biru : (strtolower($match->winner_corner) == 'merah' ? $match->sudut_merah : '-') }}</td>
                <td rowspan="5" class="w-12 big-blue">{{ $skor->skor_biru ?? 0 }}</td>
                <td rowspan="5" class="w-3">vs</td>
                <td rowspan="5" class="w-12 big-red">{{ $skor->skor_merah ?? 0 }}</td>
                <td class="w-15"></td>
                <td class="w-7"></td>
                <td class="w-7"></td>
                <td class="w-7"></td>
                <td class="w-7"></td>
            </tr>
            <tr>
                <td>Kontingen</td>
                <td>{{ strtolower($match->winner_corner) == 'biru' ? $match->kontingen_biru : (strtolower($match->winner_corner) == 'merah' ? $match->kontingen_merah : '-') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>{{ $match->kelas }} {{ strtoupper($match->golongan) }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Sudut</td>
                <td>{{ strtoupper($match->winner_corner ?? '-') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Winning By</td>
                <td>{{ $match->winning_method ?? '-' }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Time Stemp</td>
                <td>{{ $match->updated_at }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: center; height: 80px; vertical-align: top; position: relative;">
                    Ketua<br>Pertandingan<br><br><br>
                    <span style="font-size: 10px; font-weight: normal; text-transform: uppercase; white-space: nowrap;">( <span>{{ $namaPetugas['ketua'] }}</span> )</span>
                </td>
                <td style="text-align: center; height: 80px; vertical-align: top; position: relative;">
                    Dewan Wasit<br>Juri<br><br><br>
                    <span style="font-size: 10px; font-weight: normal; text-transform: uppercase; white-space: nowrap;">( <span>{{ $namaPetugas['dewan'] }}</span> )</span>
                </td>
                <td style="text-align: center; height: 80px; vertical-align: top; position: relative;">
                    Juri<br>1<br><br><br>
                    <span style="font-size: 10px; font-weight: normal; text-transform: uppercase; white-space: nowrap;">( <span>{{ $namaPetugas['juri_1'] }}</span> )</span>
                </td>
                <td style="text-align: center; height: 80px; vertical-align: top; position: relative;">
                    Juri<br>2<br><br><br>
                    <span style="font-size: 10px; font-weight: normal; text-transform: uppercase; white-space: nowrap;">( <span>{{ $namaPetugas['juri_2'] }}</span> )</span>
                </td>
                <td style="text-align: center; height: 80px; vertical-align: top; position: relative;">
                    Juri<br>3<br><br><br>
                    <span style="font-size: 10px; font-weight: normal; text-transform: uppercase; white-space: nowrap;">( <span>{{ $namaPetugas['juri_3'] }}</span> )</span>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
