@php
    $pertandinganUsecase = new \App\Http\Usecases\PertandinganUsecase();
    $result = $pertandinganUsecase->getFinal();
    $finalList = $result['data']['list'] ?? [];
@endphp

<div class="w-full space-y-4">
    
    {{-- HEADER SECTION --}}
    <div class="relative flex flex-col sm:flex-row items-center justify-center py-2 border-b border-gray-200 pb-3 min-h-[52px]">
        <div class="sm:absolute sm:left-0 mb-2 sm:mb-0">
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="h-10 sm:h-12 w-auto object-contain" onerror="this.style.display='none'">
        </div>
        <div class="text-center">
            <h1 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight leading-tight">
                Hasil Akhir Pertandingan Pencak Silat
            </h1>
            <h2 class="text-xs sm:text-sm font-semibold text-gray-600 mt-0.5">
                Kategori tanding
            </h2>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="overflow-x-auto min-w-0 w-full rounded-lg border border-gray-300">
        <table class="w-full text-xs sm:text-sm text-center border-collapse bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-800 font-bold uppercase text-[11px] tracking-wider">
                    <th class="border border-gray-300 px-2 py-2 w-10 text-center">NO</th>
                    <th class="border border-gray-300 px-2 py-2 w-20 text-center">NO PARTAI</th>
                    <th class="border border-gray-300 px-2 py-2 w-28 text-center">KELAS</th>
                    <th class="border border-gray-300 px-3 py-2 bg-blue-600 text-white font-bold w-48 text-center">BIRU</th>
                    <th class="border border-gray-300 px-3 py-2 bg-red-600 text-white font-bold w-48 text-center">MERAH</th>
                    <th class="border border-gray-300 px-2 py-2 w-24 text-center">POIN</th>
                    <th class="border border-gray-300 px-2 py-2 w-20 text-center">SUDUT</th>
                    <th class="border border-gray-300 px-2 py-2 w-20 text-center">KET</th>
                </tr>
            </thead>
            <tbody>
                @forelse($finalList as $item)
                    @php
                        $skorB = $item->final_score_biru ?? $item->skor_biru ?? 0;
                        $skorM = $item->final_score_merah ?? $item->skor_merah ?? 0;

                        $winnerCorner = strtolower($item->winner_corner ?? '');
                        if (empty($winnerCorner)) {
                            if ($skorB > $skorM) {
                                $winnerCorner = 'biru';
                            } elseif ($skorM > $skorB) {
                                $winnerCorner = 'merah';
                            } else {
                                $winnerCorner = 'seri';
                            }
                        }

                        $jkShort = '';
                        if (strtolower($item->jenis_kelamin ?? '') === 'putra') {
                            $jkShort = 'PA';
                        } elseif (strtolower($item->jenis_kelamin ?? '') === 'putri') {
                            $jkShort = 'PI';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 border-b border-gray-200 transition-colors">
                        {{-- NO --}}
                        <td class="border border-gray-300 px-2 py-2 text-center font-medium text-gray-800">
                            {{ $loop->iteration }}
                        </td>

                        {{-- NO PARTAI --}}
                        <td class="border border-gray-300 px-2 py-2 text-center font-semibold text-gray-800">
                            {{ $item->partai }}
                        </td>

                        {{-- KELAS --}}
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            <div class="font-bold text-gray-800 text-xs uppercase leading-snug">
                                {{ strtoupper($item->kelas ?? '-') }} {{ $jkShort }}
                            </div>
                            <div class="text-gray-500 text-[11px] font-medium mt-0.5 capitalize">
                                {{ ucfirst($item->golongan ?? '-') }}
                            </div>
                        </td>

                        {{-- BIRU --}}
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <div class="text-blue-600 font-bold text-xs">{{ $item->sudut_biru ?? '-' }}</div>
                            <div class="text-gray-600 text-[11px] font-medium mt-0.5">{{ $item->kontingen_biru ?? '-' }}</div>
                        </td>

                        {{-- MERAH --}}
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <div class="text-red-600 font-bold text-xs">{{ $item->sudut_merah ?? '-' }}</div>
                            <div class="text-gray-600 text-[11px] font-medium mt-0.5">{{ $item->kontingen_merah ?? '-' }}</div>
                        </td>

                        {{-- POIN --}}
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            <div class="flex items-center justify-center gap-1.5 font-bold">
                                <div class="bg-blue-600 text-white rounded px-2 py-0.5 text-xs font-bold min-w-[22px] text-center">
                                    {{ $skorB }}
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400">vs</span>
                                <div class="bg-red-600 text-white rounded px-2 py-0.5 text-xs font-bold min-w-[22px] text-center">
                                    {{ $skorM }}
                                </div>
                            </div>
                        </td>

                        {{-- SUDUT --}}
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            @if($winnerCorner === 'biru')
                                <span class="bg-blue-600 text-white rounded px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider inline-block">
                                    BIRU
                                </span>
                            @elseif($winnerCorner === 'merah')
                                <span class="bg-red-600 text-white rounded px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider inline-block">
                                    MERAH
                                </span>
                            @else
                                <span class="bg-gray-500 text-white rounded px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider inline-block">
                                    SERI
                                </span>
                            @endif
                        </td>

                        {{-- KET --}}
                        <td class="border border-gray-300 px-2 py-2 text-center font-bold text-gray-700 text-xs uppercase">
                            {{ strtoupper($item->winning_method ?? 'ANGKA') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="border border-gray-300 px-3 py-5 text-center text-gray-500 font-medium text-xs italic">
                            Belum ada hasil pertandingan final yang tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
