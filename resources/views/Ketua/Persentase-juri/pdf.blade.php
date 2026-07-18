<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Akurasi Juri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
        }
        p.subtitle {
            text-align: center;
            color: #666;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .match-container {
            margin-bottom: 30px;
            border: 1px solid #ccc;
            page-break-inside: avoid;
        }
        .match-header {
            background-color: #f4f4f4;
            padding: 10px;
            border-bottom: 1px solid #ccc;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f9f9f9;
        }
        .juri-info {
            text-align: left;
        }
        .text-green { color: #22c55e; }
        .text-yellow { color: #eab308; }
        .text-red { color: #ef4444; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; color: #666; }
    </style>
</head>
<body>

    <h2>LAPORAN AKURASI JURI PERTANDINGAN</h2>
    <p class="subtitle">
        @if($type == 'babak')
            Rekapitulasi Akurasi Juri Per Babak
        @elseif($type == 'partai')
            Rekapitulasi Akurasi Juri Per Partai
        @else
            Rekapitulasi Akurasi Juri Keseluruhan Event
        @endif
    </p>
    
    @if($type == 'event')
        <!-- AKURASI EVENT -->
        <table>
            <thead>
                <tr>
                    <th width="40%">Petugas Juri</th>
                    <th width="20%">Total Input</th>
                    <th width="20%">Total Sah</th>
                    <th width="20%">Akurasi Event</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eventJuries as $juri)
                    @php
                        $evtAcc = $juri['event_akurasi'];
                        $evtAccColor = 'text-red';
                        if ($evtAcc >= 80) $evtAccColor = 'text-green';
                        elseif ($evtAcc >= 50) $evtAccColor = 'text-yellow';
                    @endphp
                    <tr>
                        <td class="juri-info">
                            <div class="bold" style="font-size: 14px;">{{ $juri['nama_juri'] }}</div>
                        </td>
                        <td>
                            <div class="bold" style="font-size: 14px;">{{ $juri['total_input'] }}</div>
                        </td>
                        <td>
                            <div class="bold text-green" style="font-size: 14px;">{{ $juri['total_sah'] }}</div>
                        </td>
                        <td style="background-color: #f0fbff;">
                            <div class="bold {{ $evtAccColor }}" style="font-size: 16px;">{{ number_format($evtAcc, 1) }}%</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <!-- AKURASI BABAK / PARTAI -->
        @forelse($akurasiData as $match)
            <div class="match-container">
                <div class="match-header">
                    Partai: {{ $match['partai'] }} | Gelanggang: {{ strtoupper($match['gelanggang']) }} | 
                    Kelas: {{ $match['kelas'] }} ({{ ucfirst($match['golongan']) }}) | 
                    Waktu: {{ \Carbon\Carbon::parse($match['tanggal_dihitung'])->format('d M Y, H:i') }}
                </div>
                <table>
                    <thead>
                        <tr>
                            <th width="25%">Petugas Juri</th>
                            @if($type == 'babak')
                                <th width="25%">Babak 1</th>
                                <th width="25%">Babak 2</th>
                                <th width="25%">Babak 3</th>
                            @elseif($type == 'partai')
                                <th width="75%">Akurasi Partai</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($match['juris'] as $juri)
                            @php
                                $totalAcc = $juri['persentase_akurasi'];
                                $totalAccColor = 'text-red';
                                if ($totalAcc >= 80) $totalAccColor = 'text-green';
                                elseif ($totalAcc >= 50) $totalAccColor = 'text-yellow';
                            @endphp
                            <tr>
                                <td class="juri-info">
                                    <div class="bold">{{ $juri['nama_juri'] }}</div>
                                    <div class="small">{{ strtoupper(str_replace('_', ' ', $juri['posisi'])) }}</div>
                                </td>

                                @if($type == 'babak')
                                    <!-- BABAK 1 -->
                                    @php 
                                        $b1 = $juri['rounds']['babak_1'];
                                        $b1Color = $b1['akurasi'] >= 80 ? 'text-green' : ($b1['akurasi'] >= 50 ? 'text-yellow' : 'text-red');
                                    @endphp
                                    <td>
                                        <div class="bold {{ $b1Color }}" style="font-size: 16px;">{{ number_format($b1['akurasi'], 1) }}%</div>
                                        <div class="small">Sah: {{ $b1['sah'] }} / Input: {{ $b1['input'] }}</div>
                                    </td>

                                    <!-- BABAK 2 -->
                                    @php 
                                        $b2 = $juri['rounds']['babak_2'];
                                        $b2Color = $b2['akurasi'] >= 80 ? 'text-green' : ($b2['akurasi'] >= 50 ? 'text-yellow' : 'text-red');
                                    @endphp
                                    <td>
                                        <div class="bold {{ $b2Color }}" style="font-size: 16px;">{{ number_format($b2['akurasi'], 1) }}%</div>
                                        <div class="small">Sah: {{ $b2['sah'] }} / Input: {{ $b2['input'] }}</div>
                                    </td>

                                    <!-- BABAK 3 -->
                                    @php 
                                        $b3 = $juri['rounds']['babak_3'];
                                        $b3Color = $b3['akurasi'] >= 80 ? 'text-green' : ($b3['akurasi'] >= 50 ? 'text-yellow' : 'text-red');
                                    @endphp
                                    <td>
                                        <div class="bold {{ $b3Color }}" style="font-size: 16px;">{{ number_format($b3['akurasi'], 1) }}%</div>
                                        <div class="small">Sah: {{ $b3['sah'] }} / Input: {{ $b3['input'] }}</div>
                                    </td>
                                @elseif($type == 'partai')
                                    <!-- TOTAL PARTAI -->
                                    <td style="background-color: #fafafa;">
                                        <div class="bold {{ $totalAccColor }}" style="font-size: 18px;">{{ number_format($totalAcc, 1) }}%</div>
                                        <div class="small">Sah: {{ $juri['total_nilai_sah'] }} / Input: {{ $juri['total_input'] }}</div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div style="text-align: center; padding: 50px;">
                <h3>Tidak ada data akurasi juri.</h3>
            </div>
        @endforelse
    @endif

</body>
</html>
