@php
    $pertandinganUsecase = new \App\Http\Usecases\PertandinganUsecase();
    $result = $pertandinganUsecase->getFinal();
    $finalList = $result['data']['list'] ?? [];
@endphp

<div class="bg-white p-6 sm:p-10 rounded-xl shadow-sm border border-gray-200 min-w-0 w-full overflow-hidden">
    
    {{-- HEADER SECTION --}}
    <div class="flex flex-col sm:flex-row items-center justify-center relative mb-8">
        <div class="sm:absolute sm:left-0 mb-4 sm:mb-0">
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="h-20 w-auto object-contain" onerror="this.style.display='none'">
        </div>
        <div class="text-center">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-black tracking-tight">
                Hasil Akhir Pertandingan Pencak Silat
            </h1>
            <h2 class="text-xl sm:text-2xl font-bold text-black mt-1">
                Kategori tanding
            </h2>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="overflow-x-auto min-w-0 w-full rounded-lg">
        <table class="w-full min-w-[850px] text-sm text-center border-collapse border-2 border-black bg-white">
            <thead>
                <tr>
                    <th class="border border-black px-3 py-2 bg-white text-black font-bold uppercase w-12 text-center">NO</th>
                    <th class="border border-black px-3 py-2 bg-white text-black font-bold uppercase w-24 text-center">NO PARTAI</th>
                    <th class="border border-black px-3 py-2 bg-white text-black font-bold uppercase text-center w-36">KELAS</th>
                    <th class="border border-black px-3 py-2 bg-[#0000d0] text-white font-bold uppercase text-center w-64">BIRU</th>
                    <th class="border border-black px-3 py-2 bg-[#df0000] text-white font-bold uppercase text-center w-64">MERAH</th>
                    <th class="border border-black px-3 py-2 bg-white text-black font-bold uppercase w-32 text-center">POIN</th>
                    <th class="border border-black px-3 py-2 bg-white text-black font-bold uppercase w-28 text-center">SUDUT</th>
                    <th class="border border-black px-3 py-2 bg-white text-black font-bold uppercase w-28 text-center">KET</th>
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
                    <tr class="table-row hover:bg-gray-50">
                        {{-- NO --}}
                        <td class="border border-black px-3 py-3 text-center font-semibold text-black">
                            {{ $loop->iteration }}
                        </td>

                        {{-- NO PARTAI --}}
                        <td class="border border-black px-3 py-3 text-center font-semibold text-black">
                            {{ $item->partai }}
                        </td>

                        {{-- KELAS --}}
                        <td class="border border-black px-3 py-2 text-center">
                            <div class="font-bold text-black text-xs uppercase leading-snug">
                                {{ strtoupper($item->kelas ?? '-') }} {{ $jkShort }}
                            </div>
                            <div class="text-black text-xs font-semibold mt-0.5 capitalize">
                                {{ ucfirst($item->golongan ?? '-') }}
                            </div>
                        </td>

                        {{-- BIRU --}}
                        <td class="border border-black px-3 py-2 text-center">
                            <div class="text-[#0000d0] font-bold text-xs">{{ $item->sudut_biru ?? '-' }}</div>
                            <div class="text-black text-[11px] font-bold mt-0.5">{{ $item->kontingen_biru ?? '-' }}</div>
                        </td>

                        {{-- MERAH --}}
                        <td class="border border-black px-3 py-2 text-center">
                            <div class="text-[#df0000] font-bold text-xs">{{ $item->sudut_merah ?? '-' }}</div>
                            <div class="text-black text-[11px] font-bold mt-0.5">{{ $item->kontingen_merah ?? '-' }}</div>
                        </td>

                        {{-- POIN --}}
                        <td class="border border-black px-3 py-2 text-center">
                            <div class="flex items-center justify-center gap-1.5 font-bold">
                                <div class="bg-[#0000d0] text-white rounded px-2.5 py-0.5 text-xs font-bold min-w-[24px] text-center">
                                    {{ $skorB }}
                                </div>
                                <span class="text-xs font-bold text-black">vs</span>
                                <div class="bg-[#df0000] text-white rounded px-2.5 py-0.5 text-xs font-bold min-w-[24px] text-center">
                                    {{ $skorM }}
                                </div>
                            </div>
                        </td>

                        {{-- SUDUT --}}
                        <td class="border border-black px-3 py-2 text-center">
                            @if($winnerCorner === 'biru')
                                <span class="bg-[#0000d0] text-white rounded px-3 py-1 text-[11px] font-bold uppercase tracking-wider inline-block">
                                    BIRU
                                </span>
                            @elseif($winnerCorner === 'merah')
                                <span class="bg-[#df0000] text-white rounded px-3 py-1 text-[11px] font-bold uppercase tracking-wider inline-block">
                                    MERAH
                                </span>
                            @else
                                <span class="bg-gray-600 text-white rounded px-3 py-1 text-[11px] font-bold uppercase tracking-wider inline-block">
                                    SERI
                                </span>
                            @endif
                        </td>

                        {{-- KET --}}
                        <td class="border border-black px-3 py-2 text-center font-bold text-black text-xs uppercase">
                            {{ strtoupper($item->winning_method ?? 'ANGKA') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="border border-black px-3 py-8 text-center text-gray-500 font-semibold italic">
                            Belum ada hasil pertandingan final yang tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
