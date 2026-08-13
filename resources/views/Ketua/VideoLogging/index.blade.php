@extends('Ketua.Layout.app')

@section('content')
<div class="bg-white shadow-md border border-gray-200 p-4 sm:p-6 rounded-xl flex-1 flex flex-col min-w-0 w-full overflow-hidden">

    <!-- TOP HEADER -->
    <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800 tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-video text-sky-400"></i>
                <span>Video Logging Aktivitas Juri</span>
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Rekaman video aktivitas juri selama pertandingan berlangsung</p>
        </div>
        <a href="{{ route('ketua.dashboard') }}" class="flex items-center gap-2 px-3.5 py-1.5 bg-[#57d2ff] hover:bg-sky-400 text-white rounded font-bold transition shadow-sm text-xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- SEARCH & TOOLBAR -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div class="text-sm font-semibold text-gray-600">
            Total Pertandingan Terekam: <span class="text-sky-600 font-extrabold">{{ count($matches) }}</span>
        </div>

        <form method="GET" action="{{ route('ketua.video-logging') }}" class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
            </span>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari Partai / Nama Atlet..."
                class="border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 w-64">
        </form>
    </div>

    <!-- TABLE MATCHES -->
    <div class="overflow-x-auto min-w-0 w-full rounded-lg border border-gray-300 mb-8">
        <table class="w-full min-w-[800px] text-sm text-center border-collapse bg-white">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-3 py-2.5 bg-gray-100 font-bold w-16 text-center">Partai</th>
                    <th class="border border-gray-300 px-3 py-2.5 bg-gray-100 font-bold w-28 text-center">Kelas</th>
                    <th class="border border-gray-300 px-3 py-2.5 bg-blue-600 text-white font-bold w-56 text-center">Biru</th>
                    <th class="border border-gray-300 px-3 py-2.5 bg-red-600 text-white font-bold w-56 text-center">Merah</th>
                    <th class="border border-gray-300 px-3 py-2.5 bg-gray-100 font-bold text-center w-28">Total Video</th>
                    <th class="border border-gray-300 px-3 py-2.5 bg-gray-100 font-bold text-center w-36">Terakhir Rekam</th>
                    <th class="border border-gray-300 px-3 py-2.5 bg-gray-100 font-bold text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matches as $m)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="border border-gray-300 px-3 py-3 text-center font-bold">
                            {{ str_pad($m->partai ?? 0, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-center">
                            <div class="font-bold text-gray-800">Gel {{ strtoupper($m->gelanggang ?? '-') }} | {{ strtoupper($m->kelas ?? '-') }}</div>
                            <span class="inline-block mt-1 bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ ucfirst($m->golongan ?? '-') }}
                            </span>
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-center">
                            <div class="text-blue-600 font-bold text-[13px]">{{ $m->sudut_biru ?? '-' }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">{{ $m->kontingen_biru ?? '-' }}</div>
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-center">
                            <div class="text-red-600 font-bold text-[13px]">{{ $m->sudut_merah ?? '-' }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">{{ $m->kontingen_merah ?? '-' }}</div>
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold text-xs rounded-full">
                                <i class="fa-solid fa-video text-[10px]"></i>
                                <span>{{ $m->total_videos }} Video</span>
                            </span>
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-center text-xs text-gray-600 font-medium">
                            {{ \Carbon\Carbon::parse($m->last_recorded)->diffForHumans() }}
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-center">
                            <a href="{{ route('ketua.video-logging.detail', $m->match_id) }}" class="bg-sky-500 hover:bg-sky-600 text-white rounded px-3 py-1.5 font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition">
                                <i class="fa-solid fa-play text-[10px]"></i>
                                <span>Lihat</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border border-gray-300 px-3 py-8 text-center text-gray-500 font-semibold">
                            <i class="fa-solid fa-video-slash text-2xl text-gray-300 mb-2 block"></i>
                            Belum ada rekaman video aktivitas juri yang tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
