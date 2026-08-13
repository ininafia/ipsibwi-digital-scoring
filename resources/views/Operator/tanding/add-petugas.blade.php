@extends('Operator.layout.app')

@section('content')

<div x-data="{ showForm: {{ ($errors->any() || session('error') || count($list ?? []) == 0) ? 'true' : 'false' }} }" class="bg-white p-6 rounded-xl shadow border border-gray-200">

    {{-- HEADER WITH TITLE & TAMBAH BUTTON --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-sky-400">
            Data Petugas
        </h1>
        <button 
            @click="showForm = !showForm"
            type="button"
            class="bg-sky-400 hover:bg-sky-500 text-white font-semibold px-5 py-2.5 rounded-lg shadow transition flex items-center gap-2 text-sm">
            <i class="fa-solid" :class="showForm ? 'fa-minus' : 'fa-plus'"></i>
            <span x-text="showForm ? 'Tutup Form' : 'Tambah Petugas'">+ Tambah Petugas</span>
        </button>
    </div>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-5 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- FORM INPUT (TOGGLED) --}}
    <div x-show="showForm" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="mb-8 p-5 bg-sky-50/50 border border-sky-100 rounded-xl">
        <h2 class="text-lg font-bold text-sky-500 mb-4">
            Input Data Petugas Baru
        </h2>
        <form action="{{ route('operator.petugas.store') }}" method="POST">
            @csrf
            <div class="max-w-xl space-y-5">

                {{-- NIK PETUGAS --}}
                <div>
                    <label class="block text-sky-600 font-semibold text-sm mb-2">
                        NIK (Nomor Induk Kependudukan)
                    </label>
                    <input
                        type="text"
                        name="nik"
                        value="{{ old('nik') }}"
                        placeholder="Masukkan NIK (opsional / 16 digit)"
                        maxlength="20"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-300 outline-none bg-white">
                </div>

                {{-- NAMA PETUGAS --}}
                <div>
                    <label class="block text-sky-600 font-semibold text-sm mb-2">
                        Nama Petugas
                    </label>
                    <input
                        type="text"
                        name="nama"
                        value="{{ old('nama') }}"
                        placeholder="Masukkan nama"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-300 outline-none bg-white">
                </div>

            </div>

            {{-- BUTTON --}}
            <div class="mt-6 flex items-center gap-3">
                <button
                    type="submit"
                    class="bg-sky-400 hover:bg-sky-500 text-white font-semibold px-6 py-2.5 text-sm rounded-lg shadow transition">
                    Simpan Data
                </button>
                <button
                    type="button"
                    @click="showForm = false"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-5 py-2.5 text-sm rounded-lg transition">
                    Batal
                </button>
            </div>
        </form>
    </div>

    {{-- DATA TABLE --}}
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
                @forelse($list ?? [] as $index => $item)
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
