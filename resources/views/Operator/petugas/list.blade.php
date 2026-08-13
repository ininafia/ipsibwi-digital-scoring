@extends('Operator.layout.app')

@section('content')

<div class="bg-white p-6 rounded-xl shadow border border-gray-200">

    {{-- HEADER / TITLE --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-sky-400">
            Data Petugas
        </h1>
        <a href="{{ route('operator.petugas.add') }}" 
           class="bg-sky-400 hover:bg-sky-500 text-white font-semibold px-5 py-2.5 rounded-lg shadow transition flex items-center gap-2 text-sm">
            <i class="fa-solid fa-plus"></i> Tambah Petugas
        </a>
    </div>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-5 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-left text-sm text-gray-700">
            <thead class="bg-sky-50 text-sky-600 font-semibold border-b border-gray-200 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 w-16 text-center">No</th>
                    <th class="px-4 py-3">NIK</th>
                    <th class="px-4 py-3">Nama Petugas</th>
                    <th class="px-4 py-3 w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($list as $index => $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 font-mono font-medium text-gray-600">
                        {{ !empty($item->nik) ? $item->nik : '-' }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $item->nama }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('operator.petugas.edit', $item->id) }}" 
                               class="bg-amber-400 hover:bg-amber-500 text-white p-2 rounded-md text-xs transition" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('operator.petugas.delete', $item->id) }}" method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md text-xs transition" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                        Belum ada data petugas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
