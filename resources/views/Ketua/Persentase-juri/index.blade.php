@extends('Ketua.Layout.app')

@section('title', 'Persentase Akurasi Juri')

@section('sidebar')
    @include('Ketua.Layout.sidebar')
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6 print:hidden">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Riwayat Akurasi Juri</h2>
            <p class="text-sm text-gray-500 mt-1">Rekapitulasi data akurasi penilaian juri pada seluruh pertandingan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ketua.akurasi.export.all') }}" target="_blank" class="bg-[#4fcfff] hover:bg-[#3dbfe8] text-white font-bold py-2 px-4 rounded-lg text-sm transition-all shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> Export PDF Semua
            </a>
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari Partai / Juri..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4fcfff] focus:border-transparent outline-none w-64 transition-all">
            </div>
        </div>
    </div>

    <div class="space-y-4" id="akurasiContainer">
        @forelse($akurasiData as $match)
            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white match-item" id="match-{{ $match['match_id'] }}" x-data="{ expanded: false }">
                <!-- MATCH HEADER (Click to expand) -->
                <div @click="expanded = !expanded" class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors border-b border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded shadow-sm border border-gray-200 flex flex-col items-center justify-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Partai</span>
                            <span class="text-lg font-black text-[#4fcfff] leading-none">{{ $match['partai'] }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Gelanggang {{ strtoupper($match['gelanggang']) }}</h3>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">
                                Kelas {{ $match['kelas'] }} ({{ ucfirst($match['golongan']) }})
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('ketua.akurasi.export.match', $match['match_id']) }}" target="_blank" @click.stop class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1.5 px-3 rounded text-xs transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </a>
                        <span class="text-xs font-semibold text-gray-400"><i class="fa-regular fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($match['tanggal_dihitung'])->format('d M Y, H:i') }}</span>
                        <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-400 transition-transform duration-300 print:hidden" :class="expanded ? 'rotate-180' : ''">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- JURIES DETAILS (Expanded) -->
                <div x-show="expanded" x-collapse x-cloak>
                    <div class="p-4 bg-white">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="py-3 px-4 font-semibold text-gray-500 text-xs uppercase tracking-wider w-1/4">Petugas Juri</th>
                                        <th class="py-3 px-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">Babak 1</th>
                                        <th class="py-3 px-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">Babak 2</th>
                                        <th class="py-3 px-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center">Babak 3</th>
                                        <th class="py-3 px-4 font-semibold text-gray-500 text-xs uppercase tracking-wider text-center border-l border-gray-100">Total Akurasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($match['juris'] as $juri)
                                        @php
                                            $totalAcc = $juri['persentase_akurasi'];
                                            $totalAccColor = 'text-red-500';
                                            if ($totalAcc >= 80) $totalAccColor = 'text-green-500';
                                            elseif ($totalAcc >= 50) $totalAccColor = 'text-yellow-500';
                                        @endphp
                                        <tr class="hover:bg-gray-50/50">
                                            <!-- NAMA JURI -->
                                            <td class="py-4 px-4 align-top">
                                                <div class="font-bold text-gray-700 juri-name">{{ $juri['nama_juri'] }}</div>
                                                <span class="inline-block px-2 py-0.5 mt-1 bg-gray-100 text-gray-500 text-[10px] font-bold rounded uppercase">
                                                    {{ str_replace('_', ' ', $juri['posisi']) }}
                                                </span>
                                            </td>

                                            <!-- BABAK 1 -->
                                            @php 
                                                $b1 = $juri['rounds']['babak_1'];
                                                $b1Color = $b1['akurasi'] >= 80 ? 'text-green-500' : ($b1['akurasi'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                            @endphp
                                            <td class="py-4 px-4 text-center align-top">
                                                <div class="font-black text-lg {{ $b1Color }}">{{ number_format($b1['akurasi'], 1) }}%</div>
                                                <div class="text-[10px] text-gray-400 mt-1 font-semibold flex justify-center gap-2">
                                                    <span title="Masuk" class="text-green-600"><i class="fa-solid fa-check"></i> {{ $b1['sah'] }}</span>
                                                    <span title="Total Input"><i class="fa-solid fa-bullseye"></i> {{ $b1['input'] }}</span>
                                                </div>
                                            </td>

                                            <!-- BABAK 2 -->
                                            @php 
                                                $b2 = $juri['rounds']['babak_2'];
                                                $b2Color = $b2['akurasi'] >= 80 ? 'text-green-500' : ($b2['akurasi'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                            @endphp
                                            <td class="py-4 px-4 text-center align-top">
                                                <div class="font-black text-lg {{ $b2Color }}">{{ number_format($b2['akurasi'], 1) }}%</div>
                                                <div class="text-[10px] text-gray-400 mt-1 font-semibold flex justify-center gap-2">
                                                    <span title="Masuk" class="text-green-600"><i class="fa-solid fa-check"></i> {{ $b2['sah'] }}</span>
                                                    <span title="Total Input"><i class="fa-solid fa-bullseye"></i> {{ $b2['input'] }}</span>
                                                </div>
                                            </td>

                                            <!-- BABAK 3 -->
                                            @php 
                                                $b3 = $juri['rounds']['babak_3'];
                                                $b3Color = $b3['akurasi'] >= 80 ? 'text-green-500' : ($b3['akurasi'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                            @endphp
                                            <td class="py-4 px-4 text-center align-top">
                                                <div class="font-black text-lg {{ $b3Color }}">{{ number_format($b3['akurasi'], 1) }}%</div>
                                                <div class="text-[10px] text-gray-400 mt-1 font-semibold flex justify-center gap-2">
                                                    <span title="Masuk" class="text-green-600"><i class="fa-solid fa-check"></i> {{ $b3['sah'] }}</span>
                                                    <span title="Total Input"><i class="fa-solid fa-bullseye"></i> {{ $b3['input'] }}</span>
                                                </div>
                                            </td>

                                            <!-- TOTAL -->
                                            <td class="py-4 px-4 text-center align-top border-l border-gray-100 bg-gray-50/30">
                                                <div class="font-black text-xl {{ $totalAccColor }}">{{ number_format($totalAcc, 1) }}%</div>
                                                <div class="text-[11px] text-gray-500 mt-1 font-medium">
                                                    Total: <b>{{ $juri['total_input'] }}</b> <br>
                                                    Sah: <b class="text-green-600">{{ $juri['total_nilai_sah'] }}</b>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-bold text-gray-700">Tidak ada data akurasi</h3>
                <p class="text-gray-400 mt-1">Belum ada rekapitulasi data akurasi juri yang disimpan oleh sistem.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let containers = document.querySelectorAll('.match-item');
        
        containers.forEach(container => {
            let textContext = container.innerText.toLowerCase();
            if (textContext.indexOf(filter) > -1) {
                container.style.display = '';
            } else {
                container.style.display = 'none';
            }
        });
    });
</script>
@endsection
