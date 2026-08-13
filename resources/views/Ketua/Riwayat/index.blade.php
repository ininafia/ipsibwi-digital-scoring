@extends('Ketua.Layout.app')

@section('content')

<div class="bg-white shadow-md border border-gray-200 p-4 sm:p-6 rounded-xl flex-1 flex flex-col min-w-0 w-full overflow-hidden">

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- TOP BAR --}}
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
        <h2 class="text-lg font-extrabold text-gray-800 tracking-wide">Riwayat Pertandingan</h2>
        <a href="{{ route('ketua.dashboard') }}" class="flex items-center gap-2 px-3.5 py-1.5 bg-[#57d2ff] hover:bg-sky-400 text-white rounded font-bold transition shadow-sm text-xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    {{-- TAB --}}
    <div class="flex items-center justify-start sm:justify-center gap-4 sm:gap-12 md:gap-16 border-b border-gray-200 mb-6 pt-3 pb-1 overflow-x-auto whitespace-nowrap">

        <button
            id="tab-finished"
            onclick="switchTab('finished')"
            class="tab-btn {{ $tab == 'finished' ? 'border-sky-400 text-sky-500' : 'border-transparent text-gray-700' }} font-bold text-sm sm:text-[15px] py-2 px-2 sm:px-4 border-b-[3px] transition">
            FINISHED
        </button>

        <button
            id="tab-final"
            onclick="switchTab('final')"
            class="tab-btn {{ $tab == 'final' ? 'border-sky-400 text-sky-500' : 'border-transparent text-gray-700' }} font-bold text-sm sm:text-[15px] py-2 px-2 sm:px-4 border-b-[3px] transition">
            THE FINAL RESULT
        </button>

    </div>

    {{-- TOOLBAR --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">

        {{-- Entries --}}
        <div class="flex items-center gap-2">
            <select
                id="entriesPerPage"
                onchange="changeEntries(this.value)"
                class="border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-300">
                <option value="8">8</option>
                <option value="16">16</option>
                <option value="32">32</option>
            </select>
            <span class="text-sm text-gray-500">
                Entries per page
            </span>
        </div>

        {{-- Search --}}
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
            </span>
            <input
                type="text"
                id="searchInput"
                onkeyup="filterTable()"
                placeholder="Search"
                class="border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 w-64">
        </div>

    </div>

    {{-- TABLE SECTION --}}
    <div class="overflow-x-auto min-w-0 w-full rounded-lg border border-gray-300">
        <table class="w-full min-w-[800px] text-sm text-center border-collapse bg-white">
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
                @forelse($list as $item)
                    @php
                        $skorB = $item->final_score_biru ?? $item->skor_biru ?? 0;
                        $skorM = $item->final_score_merah ?? $item->skor_merah ?? 0;

                        $pemenang_sudut = '-';
                        $pemenang_warna = 'bg-gray-500';

                        if ($skorB > $skorM) {
                            $pemenang_sudut = 'BIRU';
                            $pemenang_warna = 'bg-blue-600';
                        } elseif ($skorM > $skorB) {
                            $pemenang_sudut = 'MERAH';
                            $pemenang_warna = 'bg-red-600';
                        } else {
                            $pemenang_sudut = 'SERI';
                            $pemenang_warna = 'bg-gray-500';
                        }

                        if (!empty($item->winner_corner)) {
                            $pemenang_sudut = strtoupper($item->winner_corner);
                            $pemenang_warna = (strtolower($item->winner_corner) === 'biru') ? 'bg-blue-600' : ((strtolower($item->winner_corner) === 'merah') ? 'bg-red-600' : 'bg-gray-500');
                        }
                    @endphp
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
                                <div class="bg-blue-600 text-white rounded-[4px] w-6 h-6 flex items-center justify-center text-xs">{{ $skorB }}</div>
                                <span class="text-xs font-semibold text-gray-500">vs</span>
                                <div class="bg-red-600 text-white rounded-[4px] w-6 h-6 flex items-center justify-center text-xs">{{ $skorM }}</div>
                            </div>
                        </td>

                        {{-- SUDUT PEMENANG --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <div class="{{ $pemenang_warna }} text-white rounded-[4px] px-2 py-1 font-bold text-[10px] uppercase inline-block tracking-wider">
                                {{ $pemenang_sudut }}
                            </div>
                        </td>

                        {{-- KET --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center font-semibold text-gray-700 text-[10px] uppercase">
                            {{ strtoupper($item->winning_method ?? 'ANGKA') }}
                        </td>

                        {{-- DETAIL --}}
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <a href="{{ route('ketua.riwayat.detail', $item->id) }}" class="bg-yellow-400 hover:bg-yellow-500 text-white rounded-[4px] px-3 py-1 font-bold text-[10px] uppercase shadow-sm transition tracking-wider inline-block">
                                DETAIL
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="border border-gray-300 px-3 py-6 text-center text-gray-500 font-semibold">
                            Belum ada data pertandingan riwayat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
    function switchTab(tab) {
        window.location.href = `?tab=${tab}`;
    }

    function filterTable() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let rows = document.querySelectorAll('.table-row');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }

    function changeEntries(value) {
        console.log('Entries:', value);
    }
</script>

@endsection
