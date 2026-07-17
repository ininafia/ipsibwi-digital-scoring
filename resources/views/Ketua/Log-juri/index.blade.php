@extends('Ketua.Layout.app')

@section('title', 'Log Activity Juri')

@section('sidebar')
    @include('Ketua.Layout.sidebar')
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Log Activity Juri</h2>
            <p class="text-sm text-gray-500 mt-1">Rekam jejak aktivitas juri per pertandingan.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari Partai / Juri..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4fcfff] focus:border-transparent outline-none w-64 transition-all">
            </div>
        </div>
    </div>

    <div class="space-y-4" id="logContainer">
        @forelse($groupedLogs as $matchId => $group)
            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white match-item" x-data="{ expanded: false }">
                <!-- MATCH HEADER (Click to expand) -->
                <div @click="expanded = !expanded" class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors border-b border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded shadow-sm border border-gray-200 flex flex-col items-center justify-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Partai</span>
                            <span class="text-lg font-black text-[#4fcfff] leading-none">{{ $group['match_info']['partai'] }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Gelanggang {{ strtoupper($group['match_info']['gelanggang']) }}</h3>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">
                                Kelas {{ $group['match_info']['kelas'] }} ({{ ucfirst($group['match_info']['golongan']) }})
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-semibold text-gray-500 bg-white border border-gray-200 rounded px-2 py-1 shadow-sm">{{ count($group['logs']) }} Aktivitas</span>
                        <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- LOGS DETAILS (Expanded) -->
                <div x-show="expanded" x-collapse x-cloak>
                    <div class="p-4 bg-white">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50/50">
                                        <th class="py-3 px-4 font-semibold text-gray-600 text-xs uppercase tracking-wider w-1/5">Waktu</th>
                                        <th class="py-3 px-4 font-semibold text-gray-600 text-xs uppercase tracking-wider w-1/5">Petugas Juri</th>
                                        <th class="py-3 px-4 font-semibold text-gray-600 text-xs uppercase tracking-wider text-center w-1/12">Babak</th>
                                        <th class="py-3 px-4 font-semibold text-gray-600 text-xs uppercase tracking-wider w-1/6">Aksi</th>
                                        <th class="py-3 px-4 font-semibold text-gray-600 text-xs uppercase tracking-wider w-1/6">Status</th>
                                        <th class="py-3 px-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($group['logs'] as $log)
                                        <tr class="hover:bg-gray-50/50">
                                            <!-- WAKTU -->
                                            <td class="py-3 px-4 align-top">
                                                <div class="text-xs font-semibold text-gray-700">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</div>
                                                <div class="text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s.v') }}</div>
                                            </td>

                                            <!-- NAMA JURI -->
                                            <td class="py-3 px-4 align-top">
                                                <div class="font-bold text-gray-700 text-sm log-juri-name">{{ $log->nama_juri }}</div>
                                                <span class="inline-block px-2 py-0.5 mt-0.5 bg-gray-100 text-gray-500 text-[9px] font-bold rounded uppercase">
                                                    {{ str_replace('_', ' ', $log->posisi) }}
                                                </span>
                                            </td>

                                            <!-- BABAK -->
                                            <td class="py-3 px-4 text-center align-top">
                                                <div class="font-black text-lg text-gray-700">{{ $log->id_babak }}</div>
                                            </td>

                                            <!-- AKSI -->
                                            <td class="py-3 px-4 align-top">
                                                @php
                                                    $actionClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                                    if ($log->action == 'INPUT_NILAI') {
                                                        $actionClass = 'bg-blue-50 text-blue-600 border-blue-200';
                                                    } elseif ($log->action == 'HAPUS_NILAI') {
                                                        $actionClass = 'bg-red-50 text-red-600 border-red-200';
                                                    }
                                                @endphp
                                                <span class="inline-block px-2 py-1 text-[10px] font-bold border rounded-md {{ $actionClass }}">
                                                    {{ str_replace('_', ' ', $log->action) }}
                                                </span>
                                            </td>

                                            <!-- STATUS -->
                                            <td class="py-3 px-4 align-top">
                                                @php
                                                    $statusClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                                    if ($log->status_text == 'Sah') {
                                                        $statusClass = 'bg-green-50 text-green-600 border-green-200';
                                                    } elseif ($log->status_text == 'Menunggu Validasi') {
                                                        $statusClass = 'bg-yellow-50 text-yellow-600 border-yellow-200';
                                                    } elseif (str_contains($log->status_text, 'Tidak Sah')) {
                                                        $statusClass = 'bg-red-50 text-red-600 border-red-200';
                                                    }
                                                @endphp
                                                <span class="inline-block px-2 py-1 text-[10px] font-bold border rounded-md {{ $statusClass }}">
                                                    {{ $log->status_text }}
                                                </span>
                                            </td>

                                            <!-- KETERANGAN -->
                                            <td class="py-3 px-4 align-top border-l border-gray-50 bg-gray-50/30">
                                                <div class="text-sm text-gray-600">{{ $log->description }}</div>
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
                <i class="fa-solid fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-bold text-gray-700">Tidak ada log aktivitas</h3>
                <p class="text-gray-400 mt-1">Belum ada rekam jejak aktivitas juri yang disimpan oleh sistem.</p>
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
