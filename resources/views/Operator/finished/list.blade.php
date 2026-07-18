@php
    $pertandinganUsecase = new \App\Http\Usecases\PertandinganUsecase();
    $result = $pertandinganUsecase->getFinished();
    $finishedList = $result['data']['list'] ?? [];
@endphp

<div class="overflow-x-auto w-full">

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-center border-collapse border border-gray-300 bg-white">

            <thead>
                <tr>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 font-bold w-16 text-center">Partai</th>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 font-bold w-24 text-center">Kelas</th>
                    <th class="border border-gray-300 px-3 py-2 bg-blue-600 text-white font-bold w-64 text-center">Biru</th>
                    <th class="border border-gray-300 px-3 py-2 bg-red-600 text-white font-bold w-64 text-center">Merah</th>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 font-bold text-center">Poin</th>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 font-bold text-center">Sudut</th>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 font-bold text-center">Ket</th>
                    <th class="border border-gray-300 px-3 py-2 bg-gray-100 font-bold text-center w-24">Detail</th>
                </tr>
            </thead>

            <tbody>

                @forelse($finishedList as $item)
                    <tr class="table-row border-b border-gray-200 hover:bg-gray-50">
                        
                        {{-- PARTAI --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center font-medium">
                            {{ str_pad($item->partai ?? 0, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        {{-- KELAS --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <div class="font-bold text-gray-800">
                                {{ strtoupper($item->gelanggang ?? '-') }} | {{ strtoupper($item->kelas ?? '-') }}
                            </div>
                            <span class="inline-block mt-1 bg-yellow-400 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ ucfirst($item->jenis_kelamin ?? '-') }} {{ ucfirst($item->golongan ?? '-') }}
                            </span>
                        </td>

                        {{-- BIRU --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <div class="text-blue-600 font-bold text-[13px]">{{ $item->sudut_biru ?? '-' }}</div>
                            <div class="text-gray-600 text-xs mt-0.5">{{ $item->kontingen_biru ?? '-' }}</div>
                        </td>

                        {{-- MERAH --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <div class="text-red-600 font-bold text-[13px]">{{ $item->sudut_merah ?? '-' }}</div>
                            <div class="text-gray-600 text-xs mt-0.5">{{ $item->kontingen_merah ?? '-' }}</div>
                        </td>

                        {{-- POIN --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
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
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <div class="{{ $pemenang_warna }} text-white rounded-[4px] px-2 py-1 font-bold text-[10px] uppercase inline-block tracking-wider">
                                {{ $pemenang_sudut }}
                            </div>
                        </td>

                        {{-- KET --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center font-semibold text-gray-700 text-[10px] uppercase">
                            ANGKA
                        </td>

                        {{-- DETAIL --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
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

    {{-- KETERANGAN ENTRI BAWAH KANAN --}}
    <div class="flex justify-end mt-2">
        <p class="text-gray-500 text-sm font-medium">Menampilkan 4 sampai 50 dari total 50 entri</p>
    </div>

    {{-- TOMBOL PRINT --}}
    <div class="flex justify-end mt-4 mb-2">
        <button class="bg-[#ffca28] hover:bg-[#ffb300] text-white font-bold py-2.5 px-8 rounded-[4px] shadow-sm uppercase tracking-wider transition">
            PRINT
        </button>
    </div>

</div>
