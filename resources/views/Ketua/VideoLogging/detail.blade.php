@extends('Ketua.Layout.app')

@section('content')
<div class="bg-white shadow-md border border-gray-200 p-4 sm:p-6 rounded-xl flex-1 flex flex-col min-w-0 w-full overflow-hidden">

    <!-- TOP HEADER -->
    <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800 tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-film text-sky-500"></i>
                <span>Detail Video Logging Juri</span>
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Partai {{ str_pad($match->partai ?? 0, 3, '0', STR_PAD_LEFT) }} - Tanding {{ strtoupper($match->kelas ?? '-') }} ({{ strtoupper($match->golongan ?? '-') }})</p>
        </div>
        <a href="{{ route('ketua.video-logging') }}" class="flex items-center gap-2 px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded font-bold transition shadow-sm text-xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- MATCH CARD SUMMARY -->
    <div class="flex justify-between items-center mb-6 px-6 py-4 bg-slate-900 text-white rounded-xl shadow-md">
        <!-- Biru -->
        <div class="flex items-center gap-4 w-1/3">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white shadow">B</div>
            <div>
                <p class="text-base font-bold text-blue-400 leading-tight">{{ $match->sudut_biru ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ $match->kontingen_biru ?? '-' }}</p>
            </div>
        </div>

        <!-- Tengah -->
        <div class="text-center w-1/3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">PARTAI</p>
            <p class="text-3xl font-black text-amber-400 leading-tight">{{ str_pad($match->partai ?? 0, 3, '0', STR_PAD_LEFT) }}</p>
            <p class="text-xs text-gray-300 font-semibold uppercase mt-0.5">Gelanggang {{ strtoupper($match->gelanggang ?? '-') }}</p>
        </div>

        <!-- Merah -->
        <div class="flex items-center justify-end gap-4 w-1/3">
            <div class="text-right">
                <p class="text-base font-bold text-red-400 leading-tight">{{ $match->sudut_merah ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ $match->kontingen_merah ?? '-' }}</p>
            </div>
            <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center font-bold text-white shadow">M</div>
        </div>
    </div>

    <!-- JURI VIDEO PLAYERS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        @php
            $juriList = [
                'juri_1' => 'JURI 1',
                'juri_2' => 'JURI 2',
                'juri_3' => 'JURI 3',
            ];
        @endphp

        @foreach($juriList as $pos => $label)
            @php
                $juriVideos = $videosByJuri[$pos] ?? [];
            @endphp
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                    <span class="font-extrabold text-sm text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-user-gear text-sky-500"></i>
                        <span>{{ $label }}</span>
                    </span>
                    <span class="text-xs font-semibold px-2 py-0.5 bg-sky-100 text-sky-800 rounded">
                        {{ count($juriVideos) }} Rekaman
                    </span>
                </div>

                @if(count($juriVideos) > 0)
                    @foreach($juriVideos as $idx => $v)
                        <div class="bg-white border border-gray-200 rounded-lg p-3 mb-3 shadow-sm last:mb-0">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="font-bold text-gray-700">{{ $v->nama_juri ?? $label }}</span>
                                <span class="text-gray-400 text-[11px]">{{ \Carbon\Carbon::parse($v->created_at)->format('H:i:s d/m/Y') }}</span>
                            </div>

                            <!-- VIDEO PLAYER -->
                            <div class="relative bg-black rounded-lg overflow-hidden mb-2">
                                <video controls class="w-full max-h-[220px] object-contain">
                                    <source src="{{ asset($v->file_path) }}" type="video/webm">
                                    <source src="{{ asset($v->file_path) }}" type="video/mp4">
                                    Browser Anda tidak mendukung pemutar video HTML5.
                                </video>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-gray-500 pt-1">
                                <span>Durasi: <strong class="text-gray-700">{{ $v->duration_seconds > 0 ? gmdate('i:s', $v->duration_seconds) : '-' }}</strong></span>
                                <span>Ukuran: <strong class="text-gray-700">{{ round($v->file_size / (1024 * 1024), 2) }} MB</strong></span>
                                <a href="{{ asset($v->file_path) }}" download class="text-sky-600 hover:text-sky-800 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-download"></i> Unduh
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center text-gray-400">
                        <i class="fa-solid fa-video-slash text-3xl mb-2 text-gray-300"></i>
                        <p class="text-xs font-semibold">Tidak ada rekaman untuk {{ $label }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection
