<div class="border border-gray-100 rounded-lg overflow-hidden bg-white match-item shadow-sm" x-data="{ expanded: false }">
    <!-- MATCH HEADER (Click to expand) -->
    <div @click="expanded = !expanded" class="flex items-center justify-between p-4 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50">
        <div class="flex items-center gap-6">
            <div class="flex items-baseline gap-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Partai</span>
                <span class="text-xl font-bold text-gray-700">{{ $group['match_info']['partai'] }}</span>
            </div>
            <div class="h-6 w-px bg-gray-200"></div>
            <div>
                <h3 class="font-medium text-gray-800 text-base">Gelanggang {{ strtoupper($group['match_info']['gelanggang']) }}</h3>
                <p class="text-xs text-gray-400 font-medium mt-0.5">
                    Kelas {{ $group['match_info']['kelas'] }} ({{ ucfirst($group['match_info']['golongan']) }})
                </p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            @php
                $totalAktivitas = 0;
                if(isset($group['babak'])) {
                    foreach($group['babak'] as $bData) {
                        if(isset($bData['clusters'])) {
                            foreach($bData['clusters'] as $cluster) {
                                $totalAktivitas += count($cluster['events'] ?? []);
                            }
                        }
                    }
                }
            @endphp
            <span class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1 shadow-sm">{{ $totalAktivitas }} Aktivitas</span>
            <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-400 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''">
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>
    </div>

    <!-- LOGS DETAILS (Expanded) -->
    <div x-show="expanded" x-collapse x-cloak>
        <div class="p-4 bg-white">
            <div class="space-y-12">
                @if(isset($group['babak']))
                    @foreach($group['babak'] as $bData)
                        <div class="border border-gray-100 rounded-xl p-4 relative pt-6 bg-white shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
                            <div class="absolute -top-3 left-4 bg-[#0f172a] text-white px-3 py-1 rounded-md text-xs font-bold tracking-wider shadow-sm">BABAK {{ $bData['babak_ke'] }}</div>
                            @include('Ketua.Log-juri.log-timeline', ['clusters' => $bData['clusters']])
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
