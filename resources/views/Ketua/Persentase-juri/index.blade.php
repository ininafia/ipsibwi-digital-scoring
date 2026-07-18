@extends('Ketua.Layout.app')

@section('title', 'Persentase Akurasi Juri')

@section('sidebar')
    @include('Ketua.Layout.sidebar')
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{ activeTab: 'babak' }">
    <div class="flex justify-between items-center mb-4 print:hidden">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Riwayat Akurasi Juri</h2>
            <p class="text-sm text-gray-500 mt-1">Rekapitulasi data akurasi penilaian juri pada seluruh pertandingan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a :href="'{{ route('ketua.akurasi.export.all') }}?type=' + activeTab" target="_blank" class="bg-[#4fcfff] hover:bg-[#3dbfe8] text-white font-medium py-1.5 px-3 rounded-md text-xs transition-all shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export PDF <span x-text="activeTab === 'babak' ? 'Babak' : (activeTab === 'partai' ? 'Partai' : 'Event')"></span></span>
            </a>
            <div class="relative" x-show="activeTab !== 'event'">
                <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari Partai / Juri..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4fcfff] focus:border-transparent outline-none w-64 transition-all">
            </div>
            <div class="relative" x-show="activeTab === 'event'">
                <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchEventInput" placeholder="Cari Nama Juri..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4fcfff] focus:border-transparent outline-none w-64 transition-all">
            </div>
        </div>
    </div>

    <!-- TABS MENU -->
    <div class="flex border-b border-gray-200 mb-6 print:hidden">
        <button @click="activeTab = 'babak'" 
                :class="activeTab === 'babak' ? 'border-[#4fcfff] text-[#4fcfff]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="px-6 py-3 font-semibold text-sm border-b-2 transition-colors">
            Akurasi Per Babak
        </button>
        <button @click="activeTab = 'partai'" 
                :class="activeTab === 'partai' ? 'border-[#4fcfff] text-[#4fcfff]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="px-6 py-3 font-semibold text-sm border-b-2 transition-colors">
            Akurasi Per Partai
        </button>
        <button @click="activeTab = 'event'" 
                :class="activeTab === 'event' ? 'border-[#4fcfff] text-[#4fcfff]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="px-6 py-3 font-semibold text-sm border-b-2 transition-colors">
            Total Keseluruhan Event
        </button>
    </div>

    <!-- TAB 1 & 2: PER BABAK / PER PARTAI -->
    <div class="space-y-2" id="akurasiContainer" x-show="activeTab === 'babak' || activeTab === 'partai'">
        @forelse($akurasiData as $match)
            <div class="border border-gray-100 rounded-lg overflow-hidden bg-white match-item shadow-sm" id="match-{{ $match['match_id'] }}" x-data="{ expanded: false }">
                <!-- MATCH HEADER (Click to expand) -->
                <div @click="expanded = !expanded" class="flex items-center justify-between p-4 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50">
                    <div class="flex items-center gap-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Partai</span>
                            <span class="text-xl font-bold text-gray-700">{{ $match['partai'] }}</span>
                        </div>
                        <div class="h-6 w-px bg-gray-200"></div>
                        <div>
                            <h3 class="font-medium text-gray-800 text-base">Gelanggang {{ strtoupper($match['gelanggang']) }}</h3>
                            <p class="text-xs text-gray-400 font-medium mt-0.5">
                                Kelas {{ $match['kelas'] }} ({{ ucfirst($match['golongan']) }})
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <a :href="'{{ route('ketua.akurasi.export.match', $match['match_id']) }}?type=' + activeTab" target="_blank" @click.stop class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-1 px-2.5 rounded text-xs transition-all flex items-center gap-1.5 whitespace-nowrap">
                            <i class="fa-solid fa-file-pdf"></i> <span>Export PDF</span>
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
                                    <tr class="border-b border-gray-100">
                                        <th class="py-3 px-4 font-medium text-gray-500 text-sm w-1/4">Petugas Juri</th>
                                        <!-- KOLOM BABAK -->
                                        <template x-if="activeTab === 'babak'">
                                            <th class="py-3 px-4 font-medium text-gray-500 text-sm text-center">Babak 1</th>
                                        </template>
                                        <template x-if="activeTab === 'babak'">
                                            <th class="py-3 px-4 font-medium text-gray-500 text-sm text-center">Babak 2</th>
                                        </template>
                                        <template x-if="activeTab === 'babak'">
                                            <th class="py-3 px-4 font-medium text-gray-500 text-sm text-center">Babak 3</th>
                                        </template>
                                        <!-- KOLOM PARTAI -->
                                        <template x-if="activeTab === 'partai'">
                                            <th class="py-3 px-4 font-medium text-[#4fcfff] text-sm text-center">Akurasi Partai</th>
                                        </template>
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
                                            <td class="py-4 px-4 align-top w-1/4">
                                                <div class="font-semibold text-gray-800 text-sm juri-name">{{ $juri['nama_juri'] }}</div>
                                                <span class="inline-block px-2.5 py-0.5 mt-1.5 bg-gray-50 border border-gray-200 text-gray-500 text-[10px] font-medium rounded-full uppercase tracking-wide">
                                                    {{ str_replace('_', ' ', $juri['posisi']) }}
                                                </span>
                                            </td>

                                            <!-- BABAK 1 -->
                                            <td class="py-4 px-4 text-center align-top" x-show="activeTab === 'babak'">
                                                @php 
                                                    $b1 = $juri['rounds']['babak_1'];
                                                    $b1Color = $b1['akurasi'] >= 80 ? 'text-green-500' : ($b1['akurasi'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                                @endphp
                                                <div class="font-semibold text-sm {{ $b1Color }}">{{ number_format($b1['akurasi'], 1) }}%</div>
                                                <div class="flex justify-center items-center gap-3 mt-1.5">
                                                    <div class="flex items-center gap-1.5" title="Sah">
                                                        <i class="fa-solid fa-check text-green-500 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $b1['sah'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5" title="Total Input">
                                                        <i class="fa-solid fa-bullseye text-gray-400 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $b1['input'] }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- BABAK 2 -->
                                            <td class="py-4 px-4 text-center align-top" x-show="activeTab === 'babak'">
                                                @php 
                                                    $b2 = $juri['rounds']['babak_2'];
                                                    $b2Color = $b2['akurasi'] >= 80 ? 'text-green-500' : ($b2['akurasi'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                                @endphp
                                                <div class="font-semibold text-sm {{ $b2Color }}">{{ number_format($b2['akurasi'], 1) }}%</div>
                                                <div class="flex justify-center items-center gap-3 mt-1.5">
                                                    <div class="flex items-center gap-1.5" title="Sah">
                                                        <i class="fa-solid fa-check text-green-500 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $b2['sah'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5" title="Total Input">
                                                        <i class="fa-solid fa-bullseye text-gray-400 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $b2['input'] }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- BABAK 3 -->
                                            <td class="py-4 px-4 text-center align-top" x-show="activeTab === 'babak'">
                                                @php 
                                                    $b3 = $juri['rounds']['babak_3'];
                                                    $b3Color = $b3['akurasi'] >= 80 ? 'text-green-500' : ($b3['akurasi'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                                @endphp
                                                <div class="font-semibold text-sm {{ $b3Color }}">{{ number_format($b3['akurasi'], 1) }}%</div>
                                                <div class="flex justify-center items-center gap-3 mt-1.5">
                                                    <div class="flex items-center gap-1.5" title="Sah">
                                                        <i class="fa-solid fa-check text-green-500 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $b3['sah'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5" title="Total Input">
                                                        <i class="fa-solid fa-bullseye text-gray-400 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $b3['input'] }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- TOTAL PARTAI -->
                                            <td class="py-4 px-4 text-center align-middle" x-show="activeTab === 'partai'">
                                                <div class="font-semibold text-base {{ $totalAccColor }}">{{ number_format($totalAcc, 1) }}%</div>
                                                <div class="flex justify-center items-center gap-3 mt-1.5">
                                                    <div class="flex items-center gap-1.5" title="Sah">
                                                        <i class="fa-solid fa-check text-green-500 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $juri['total_nilai_sah'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5" title="Total Input">
                                                        <i class="fa-solid fa-bullseye text-gray-400 text-[10px]"></i>
                                                        <span class="text-xs font-semibold text-gray-600">{{ $juri['total_input'] }}</span>
                                                    </div>
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

    <!-- TAB 3: AKURASI EVENT -->
    <div x-show="activeTab === 'event'" x-cloak id="akurasiEventContainer">
        @if(empty($eventJuries))
            <div class="py-16 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-bold text-gray-700">Tidak ada data juri</h3>
                <p class="text-gray-400 mt-1">Belum ada rekapitulasi data akurasi untuk event ini.</p>
            </div>
        @else
            <div class="overflow-x-auto border border-gray-100 rounded-lg shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-4 px-6 font-medium text-gray-500 text-sm w-1/3">Petugas Juri</th>
                            <th class="py-4 px-6 font-medium text-gray-500 text-sm text-center">Total Input Event</th>
                            <th class="py-4 px-6 font-medium text-gray-500 text-sm text-center">Total Sah Event</th>
                            <th class="py-4 px-6 font-medium text-[#4fcfff] text-sm text-center">Akurasi Event</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($eventJuries as $juri)
                            @php
                                $evtAcc = $juri['event_akurasi'];
                                $evtAccColor = 'text-red-500';
                                if ($evtAcc >= 80) $evtAccColor = 'text-green-500';
                                elseif ($evtAcc >= 50) $evtAccColor = 'text-yellow-500';
                            @endphp
                            <tr class="hover:bg-gray-50/50 event-juri-item">
                                <td class="py-3 px-4 font-semibold text-gray-800 text-base juri-name-event">{{ $juri['nama_juri'] }}</td>
                                <td class="py-3 px-4 text-center font-medium text-gray-600 text-sm">{{ $juri['total_input'] }}</td>
                                <td class="py-3 px-4 text-center font-medium text-green-600 text-sm">{{ $juri['total_sah'] }}</td>
                                <td class="py-3 px-4 text-center border-l border-gray-200">
                                    <div class="font-bold text-lg {{ $evtAccColor }}">{{ number_format($evtAcc, 1) }}%</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Pencarian untuk tab Babak & Partai
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

    // Pencarian untuk tab Event
    document.getElementById('searchEventInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let containers = document.querySelectorAll('.event-juri-item');
        
        containers.forEach(container => {
            let textContext = container.querySelector('.juri-name-event').innerText.toLowerCase();
            if (textContext.indexOf(filter) > -1) {
                container.style.display = '';
            } else {
                container.style.display = 'none';
            }
        });
    });
</script>
@endsection
