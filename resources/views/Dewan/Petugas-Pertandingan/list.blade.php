@extends('Dewan.Layout.app')

@section('title', 'Daftar Penugasan Petugas')

@section('sidebar')
    @include('Dewan.Layout.sidebar-petugas')
@endsection

@section('content')
    <h1 class="text-[28px] font-bold text-[#62cbf5] mb-6">Daftar Penugasan Petugas</h1>
    
    {{-- SUCCESS --}}
    @if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-md text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-md shadow-sm p-8 max-w-[100%] mb-10 border border-blue-500">
        
        <!-- FILTER TABS -->
        <div class="flex items-center gap-4 border-b border-gray-200 mb-6 pb-2">
            <a href="{{ route('dewan.petugas', ['filter' => 'active']) }}" 
               class="px-4 py-2 font-semibold text-sm transition-all {{ (!isset($filter) || $filter === 'active') ? 'text-[#62cbf5] border-b-2 border-[#62cbf5]' : 'text-gray-500 hover:text-gray-700' }}">
                Belum Selesai (Aktif)
            </a>
            <a href="{{ route('dewan.petugas', ['filter' => 'finished']) }}" 
               class="px-4 py-2 font-semibold text-sm transition-all {{ (isset($filter) && $filter === 'finished') ? 'text-[#62cbf5] border-b-2 border-[#62cbf5]' : 'text-gray-500 hover:text-gray-700' }}">
                Sudah Selesai
            </a>
            <a href="{{ route('dewan.petugas', ['filter' => 'all']) }}" 
               class="px-4 py-2 font-semibold text-sm transition-all {{ (isset($filter) && $filter === 'all') ? 'text-[#62cbf5] border-b-2 border-[#62cbf5]' : 'text-gray-500 hover:text-gray-700' }}">
                Semua
            </a>
        </div>

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <select class="border border-gray-300 rounded px-2 py-1 bg-gray-200 text-sm">
                    <option>8</option>
                    <option>10</option>
                    <option>20</option>
                </select>
                <span class="text-sm text-gray-400">Entries per page</span>
            </div>

            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input type="text" placeholder="Search" class="border border-gray-400 rounded-md py-1.5 pl-10 pr-3 text-sm focus:outline-none focus:border-[#62cbf5]">
                </div>
                
                <a href="{{ route('dewan.petugas.add') }}" class="bg-[#82c6ef] hover:bg-[#62cbf5] text-white font-bold py-2 px-4 rounded-md transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-plus font-bold text-xl"></i> Tambah Data
                </a>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm text-center border-collapse border border-black bg-white">
                <thead>
                    <tr class="bg-[#a2d8f3]">
                        <th class="border border-black py-3 font-extrabold text-black w-16">Partai</th>
                        <th class="border border-black py-3 font-extrabold text-black w-24">Gelanggang</th>
                        <th class="border border-black py-3 font-extrabold text-black">KP</th>
                        <th class="border border-black py-3 font-extrabold text-black">DT</th>
                        <th class="border border-black py-3 font-extrabold text-black">Dewan</th>
                        <th class="border border-black py-3 font-extrabold text-black">Juri 1</th>
                        <th class="border border-black py-3 font-extrabold text-black">Juri 2</th>
                        <th class="border border-black py-3 font-extrabold text-black">Juri 3</th>
                        <th class="border border-black py-3 font-extrabold text-black">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedList as $item)
                        <tr class="border-b border-black last:border-b-0 hover:bg-gray-50 text-[13px]">
                            <td class="border border-black py-3 text-black font-semibold">{{ $item['partai'] }}</td>
                            <td class="border border-black py-3 text-black font-semibold text-lg">{{ strtoupper($item['gelanggang']) }}</td>
                            <td class="border border-black py-3 text-black">{{ $item['ketua'] }}</td>
                            <td class="border border-black py-3 text-black">{{ $item['delegasi_teknik'] }}</td>
                            <td class="border border-black py-3 text-black">{{ $item['dewan'] }}</td>
                            <td class="border border-black py-3 text-black">{{ $item['juri1'] }}</td>
                            <td class="border border-black py-3 text-black">{{ $item['juri2'] }}</td>
                            <td class="border border-black py-3 text-black">{{ $item['juri3'] }}</td>
                            <td class="border border-black py-3 text-black">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('dewan.petugas.run', $item['id']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1.5 px-3 rounded text-xs shadow flex items-center gap-1" title="Mulai">
                                            <i class="fa-solid fa-play"></i> Mulai
                                        </button>
                                    </form>
                                    <form action="{{ route('dewan.petugas.delete', $item['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penugasan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded text-xs shadow flex items-center gap-1" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="border border-black py-6 text-center text-gray-500">
                                Belum ada petugas yang ditugaskan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
