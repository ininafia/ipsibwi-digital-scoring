@php
    $pertandinganUsecase = new \App\Http\Usecases\PertandinganUsecase();
    $result = $pertandinganUsecase->getFinished();
    $finishedList = $result['data']['list'] ?? [];
@endphp

<div class="overflow-x-auto flex-1 min-w-0 w-full rounded-lg border border-gray-300">
    <table class="w-full min-w-[800px] text-sm text-center border-collapse bg-white">

            <thead>
                <tr>
                    <th class="border border-gray-300 px-3 py-1.5 bg-gray-100 font-bold w-16 text-center">Partai</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-gray-100 font-bold w-24 text-center">Kelas</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-blue-600 text-white font-bold w-64 text-center">Biru</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-red-600 text-white font-bold w-64 text-center">Merah</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-gray-100 font-bold text-center">Poin</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-gray-100 font-bold text-center">Sudut</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-gray-100 font-bold text-center">Ket</th>
                    <th class="border border-gray-300 px-3 py-1.5 bg-gray-100 font-bold text-center w-24">Detail</th>
                </tr>
            </thead>

            <tbody>

                @forelse($finishedList as $item)
                    <tr class="table-row border-b border-gray-200 hover:bg-gray-50">
                        
                        {{-- PARTAI --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center font-medium">
                            {{ str_pad($item->partai ?? 0, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        {{-- KELAS --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center">
                            <div class="font-bold text-gray-800">
                                {{ strtoupper($item->gelanggang ?? '-') }} | {{ strtoupper($item->kelas ?? '-') }}
                            </div>
                            <span class="inline-block bg-yellow-400 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ ucfirst($item->jenis_kelamin ?? '-') }} {{ ucfirst($item->golongan ?? '-') }}
                            </span>
                        </td>

                        {{-- BIRU --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center">
                            <div class="text-blue-600 font-bold text-[13px]">{{ $item->sudut_biru ?? '-' }}</div>
                            <div class="text-gray-600 text-xs">{{ $item->kontingen_biru ?? '-' }}</div>
                        </td>

                        {{-- MERAH --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center">
                            <div class="text-red-600 font-bold text-[13px]">{{ $item->sudut_merah ?? '-' }}</div>
                            <div class="text-gray-600 text-xs">{{ $item->kontingen_merah ?? '-' }}</div>
                        </td>

                        {{-- POIN --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center">
                            <div class="flex items-center justify-center gap-2 font-bold">
                                <div class="bg-blue-600 text-white rounded-[4px] w-6 h-6 flex items-center justify-center text-xs">{{ $item->skor_biru ?? 0 }}</div>
                                <span class="text-xs font-semibold text-gray-500">vs</span>
                                <div class="bg-red-600 text-white rounded-[4px] w-6 h-6 flex items-center justify-center text-xs">{{ $item->skor_merah ?? 0 }}</div>
                            </div>
                        </td>

                        {{-- SUDUT PEMENANG --}}
                        @php
                            $pemenang_sudut = '-';
                            $pemenang_warna = 'bg-gray-500';
                            
                            if (($item->skor_biru ?? 0) > ($item->skor_merah ?? 0)) {
                                $pemenang_sudut = 'BIRU';
                                $pemenang_warna = 'bg-blue-600';
                            } elseif (($item->skor_merah ?? 0) > ($item->skor_biru ?? 0)) {
                                $pemenang_sudut = 'MERAH';
                                $pemenang_warna = 'bg-red-600';
                            } else {
                                $pemenang_sudut = 'SERI';
                                $pemenang_warna = 'bg-gray-500';
                            }
                        @endphp
                        <td class="border border-gray-300 px-3 py-1.5 text-center">
                            <div class="{{ $pemenang_warna }} text-white rounded-[4px] px-2 py-0.5 font-bold text-[10px] uppercase inline-block tracking-wider">
                                {{ $pemenang_sudut }}
                            </div>
                        </td>

                        {{-- KET --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center font-semibold text-gray-700 text-[10px] uppercase">
                            ANGKA
                        </td>

                        {{-- DETAIL --}}
                        <td class="border border-gray-300 px-3 py-1.5 text-center">
                            <a href="{{ route('operator.tanding.finished.detail', $item->id) }}" class="bg-yellow-400 hover:bg-yellow-500 text-white rounded-[4px] px-3 py-1 font-bold text-[10px] uppercase shadow-sm transition tracking-wider inline-block">
                                DETAIL
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="border border-gray-300 px-3 py-6 text-center text-gray-500 font-semibold">
                            Belum ada data pertandingan yang selesai.
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

    {{-- FOOTER: KETERANGAN ENTRI & TOMBOL PRINT --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-3 pt-2 border-t border-gray-100 shrink-0">
        <p class="text-gray-500 text-xs font-medium">Menampilkan {{ count($finishedList) }} dari total {{ count($finishedList) }} entri</p>
        <button onclick="window.print()" class="bg-[#ffca28] hover:bg-[#ffb300] text-white font-bold py-1.5 px-6 rounded text-xs shadow-sm uppercase tracking-wider transition flex items-center gap-1.5">
            <i class="fa-solid fa-print text-[11px]"></i>
            <span>PRINT</span>
        </button>
    </div>
