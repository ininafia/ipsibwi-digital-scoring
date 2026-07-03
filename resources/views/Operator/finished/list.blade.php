@php
    $pertandinganUsecase = new \App\Http\Usecases\PertandinganUsecase();
    $result = $pertandinganUsecase->getFinished();
    $finishedList = $result['data']['list'] ?? [];
@endphp

<div class="overflow-x-auto w-full">

    {{-- BORDER BIRU SEPERTI DI GAMBAR --}}
    <div class="border-[3px] border-gray-300 rounded-sm p-[1px]">
        
        <table class="w-full text-sm text-center border-collapse bg-white">

            <thead>
                <tr class="border-b border-gray-300">
                    <th class="border-r border-gray-300 py-3 font-extrabold text-black w-24 uppercase">Partai</th>
                    <th class="border-r border-gray-300 py-3 font-extrabold text-black w-28 uppercase">Kelas</th>
                    <th class="border-r border-gray-300 py-3 font-extrabold text-white bg-blue-700 uppercase w-64">Biru</th>
                    <th class="border-r border-gray-300 py-3 font-extrabold text-white bg-red-600 uppercase w-64">Merah</th>
                    <th class="border-r border-gray-300 py-3 font-extrabold text-black uppercase">Poin</th>
                    <th class="border-r border-gray-300 py-3 font-extrabold text-black uppercase">Sudut</th>
                    <th class="border-r border-gray-300 py-3 font-extrabold text-black uppercase">Ket</th>
                    <th class="py-3 font-extrabold text-black uppercase">Detail</th>
                </tr>
            </thead>

            <tbody>

                @forelse($finishedList as $item)
                    <tr class="border-b border-gray-300 last:border-b-0 hover:bg-gray-50">
                        
                        {{-- PARTAI --}}
                        <td class="border-r border-gray-300 py-3 text-black font-semibold">
                            {{ str_pad($item->partai ?? 0, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        {{-- KELAS --}}
                        <td class="border-r border-gray-300 py-3 text-black">
                            <div class="font-semibold text-gray-800">
                                {{ strtoupper($item->gelanggang ?? '-') }} | {{ strtoupper($item->kelas ?? '-') }}
                            </div>
                            <span class="inline-block mt-1 bg-yellow-400 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ ucfirst($item->jenis_kelamin ?? '-') }} {{ ucfirst($item->golongan ?? '-') }}
                            </span>
                        </td>

                        {{-- BIRU --}}
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-blue-700 font-bold text-[13px]">{{ $item->sudut_biru ?? '-' }}</div>
                            <div class="text-black font-bold text-[11px]">{{ $item->kontingen_biru ?? '-' }}</div>
                        </td>

                        {{-- MERAH --}}
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-red-600 font-bold text-[13px]">{{ $item->sudut_merah ?? '-' }}</div>
                            <div class="text-black font-bold text-[11px]">{{ $item->kontingen_merah ?? '-' }}</div>
                        </td>

                        {{-- POIN --}}
                        <td class="border-r border-gray-300 py-3">
                            <div class="flex items-center justify-center gap-2 font-bold">
                                <div class="bg-[#00008b] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">{{ $item->skor_biru ?? 0 }}</div>
                                <span class="text-sm font-semibold text-black">vs</span>
                                <div class="bg-[#cc0000] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">{{ $item->skor_merah ?? 0 }}</div>
                            </div>
                        </td>

                        {{-- SUDUT PEMENANG --}}
                        @php
                            $pemenang_sudut = '-';
                            $pemenang_warna = 'bg-gray-500';
                            
                            if (($item->skor_biru ?? 0) > ($item->skor_merah ?? 0)) {
                                $pemenang_sudut = 'BIRU';
                                $pemenang_warna = 'bg-[#00008b]';
                            } elseif (($item->skor_merah ?? 0) > ($item->skor_biru ?? 0)) {
                                $pemenang_sudut = 'MERAH';
                                $pemenang_warna = 'bg-[#cc0000]';
                            } else {
                                $pemenang_sudut = 'SERI';
                                $pemenang_warna = 'bg-gray-700';
                            }
                        @endphp
                        <td class="border-r border-gray-300 py-3">
                            <div class="{{ $pemenang_warna }} text-white rounded-[4px] px-3 py-1.5 font-bold text-xs uppercase inline-block tracking-wider">
                                {{ $pemenang_sudut }}
                            </div>
                        </td>

                        {{-- KET --}}
                        <td class="border-r border-gray-300 py-3 font-semibold text-black text-xs uppercase">
                            ANGKA
                        </td>

                        {{-- DETAIL --}}
                        <td class="py-3">
                            <a href="{{ route('operator.tanding.finished.detail', $item->id) }}" class="bg-[#ffca28] hover:bg-[#ffb300] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase shadow-sm transition tracking-wider inline-block">
                                DETAIL
                            </a>
                        </td>

                    </tr>
                @empty
                    {{-- DUMMY DATA AGAR SAMA PERSIS DENGAN GAMBAR JIKA DATA KOSONG --}}
                    
                    {{-- ROW 1 --}}
                    <tr class="border-b border-gray-300 hover:bg-gray-50">
                        <td class="border-r border-gray-300 py-4 text-black font-semibold text-sm">A | 50</td>
                        <td class="border-r border-gray-300 py-3 text-black text-[13px] leading-snug">D PA<br>Dewasa</td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-blue-700 font-bold text-[13px]">Salsabila Ds</div>
                            <div class="text-black font-bold text-[11px]">SMP Darus Sholah</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-red-600 font-bold text-[13px]">Nabila Ayu</div>
                            <div class="text-black font-bold text-[11px]">SMP Ahmad Yani</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="flex items-center justify-center gap-2 font-bold">
                                <div class="bg-[#00008b] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">26</div>
                                <span class="text-sm font-semibold text-black">vs</span>
                                <div class="bg-[#cc0000] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">17</div>
                            </div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="bg-[#00008b] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase inline-block tracking-wider">BIRU</div>
                        </td>
                        <td class="border-r border-gray-300 py-3 font-semibold text-black text-xs uppercase">ANGKA</td>
                        <td class="py-3">
                            <a href="{{ route('operator.tanding.finished.detail', $item->id) }}" class="bg-[#ffca28] hover:bg-[#ffb300] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase shadow-sm transition tracking-wider inline-block">DETAIL</a>
                        </td>
                    </tr>

                    {{-- ROW 2 --}}
                    <tr class="border-b border-gray-300 hover:bg-gray-50">
                        <td class="border-r border-gray-300 py-4 text-black font-semibold text-sm">B | 50</td>
                        <td class="border-r border-gray-300 py-3 text-black text-[13px] leading-snug">D PA<br>Dewasa</td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-blue-700 font-bold text-[13px]">Salsabila Ds</div>
                            <div class="text-black font-bold text-[11px]">SMP Darus Sholah</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-red-600 font-bold text-[13px]">Nabila Ayu</div>
                            <div class="text-black font-bold text-[11px]">SMP Ahmad Yani</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="flex items-center justify-center gap-2 font-bold">
                                <div class="bg-[#00008b] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">17</div>
                                <span class="text-sm font-semibold text-black">vs</span>
                                <div class="bg-[#cc0000] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">26</div>
                            </div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="bg-[#cc0000] text-white rounded-[4px] px-3 py-1.5 font-bold text-xs capitalize inline-block tracking-wider">Merah</div>
                        </td>
                        <td class="border-r border-gray-300 py-3 font-semibold text-black text-xs uppercase">TEKNIK</td>
                        <td class="py-3">
                            <a href="{{ route('operator.tanding.finished.detail', $item->id ?? 1) }}" class="bg-[#ffca28] hover:bg-[#ffb300] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase shadow-sm transition tracking-wider inline-block">DETAIL</a>
                        </td>
                    </tr>

                    {{-- ROW 3 --}}
                    <tr class="border-b border-gray-300 hover:bg-gray-50">
                        <td class="border-r border-gray-300 py-4 text-black font-semibold text-sm">C | 50</td>
                        <td class="border-r border-gray-300 py-3 text-black text-[13px] leading-snug">D PA<br>Dewasa</td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-blue-700 font-bold text-[13px]">Salsabila Ds</div>
                            <div class="text-black font-bold text-[11px]">SMP Darus Sholah</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-red-600 font-bold text-[13px]">Nabila Ayu</div>
                            <div class="text-black font-bold text-[11px]">SMP Ahmad Yani</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="flex items-center justify-center gap-2 font-bold">
                                <div class="bg-[#00008b] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">26</div>
                                <span class="text-sm font-semibold text-black">vs</span>
                                <div class="bg-[#cc0000] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">17</div>
                            </div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="bg-[#00008b] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase inline-block tracking-wider">BIRU</div>
                        </td>
                        <td class="border-r border-gray-300 py-3 font-semibold text-black text-xs uppercase">MUTLAK</td>
                        <td class="py-3">
                            <a href="{{ route('operator.tanding.finished.detail', $item->id ?? 2) }}" class="bg-[#ffca28] hover:bg-[#ffb300] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase shadow-sm transition tracking-wider inline-block">DETAIL</a>
                        </td>
                    </tr>

                    {{-- ROW 4 --}}
                    <tr class="hover:bg-gray-50">
                        <td class="border-r border-gray-300 py-4 text-black font-semibold text-sm">D | 50</td>
                        <td class="border-r border-gray-300 py-3 text-black text-[13px] leading-snug">D PA<br>Dewasa</td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-blue-700 font-bold text-[13px]">Salsabila Ds</div>
                            <div class="text-black font-bold text-[11px]">SMP Darus Sholah</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="text-red-600 font-bold text-[13px]">Nabila Ayu</div>
                            <div class="text-black font-bold text-[11px]">SMP Ahmad Yani</div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="flex items-center justify-center gap-2 font-bold">
                                <div class="bg-[#00008b] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">17</div>
                                <span class="text-sm font-semibold text-black">vs</span>
                                <div class="bg-[#cc0000] text-white rounded-[4px] w-7 h-7 flex items-center justify-center text-sm">26</div>
                            </div>
                        </td>
                        <td class="border-r border-gray-300 py-3">
                            <div class="bg-[#cc0000] text-white rounded-[4px] px-3 py-1.5 font-bold text-xs capitalize inline-block tracking-wider">Merah</div>
                        </td>
                        <td class="border-r border-gray-300 py-3 font-semibold text-black text-xs uppercase">UNDUR<br>DIRI</td>
                        <td class="py-3">
                            <a href="{{ route('operator.tanding.finished.detail', $item->id ?? 3) }}" class="bg-[#ffca28] hover:bg-[#ffb300] text-white rounded-[4px] px-4 py-1.5 font-bold text-xs uppercase shadow-sm transition tracking-wider inline-block">DETAIL</a>
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
