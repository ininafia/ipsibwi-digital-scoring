@extends('Operator.layout.app')

@section('content')

<div class="bg-white p-6 rounded-xl shadow border border-gray-200">

    {{-- TITLE --}}
    <h1 class="text-2xl font-bold text-sky-400 mb-6">
        Edit Data Petugas
    </h1>

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

    {{-- FORM --}}
    <form action="{{ route('operator.petugas.update', $petugas['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="max-w-xl space-y-6">

            {{-- NIK PETUGAS --}}
            <div>
                <label class="block text-sky-500 font-semibold text-sm mb-2">
                    NIK (Nomor Induk Kependudukan)
                </label>
                <input
                    type="text"
                    name="nik"
                    value="{{ old('nik', $petugas['nik'] ?? '') }}"
                    placeholder="Masukkan NIK (opsional / 16 digit)"
                    maxlength="20"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-sky-300 outline-none">
            </div>

            {{-- NAMA PETUGAS --}}
            <div>
                <label class="block text-sky-500 font-semibold text-sm mb-2">
                    Nama
                </label>
                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama', $petugas['nama'] ?? '') }}"
                    placeholder="Masukkan nama"
                    oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-sky-300 outline-none">
            </div>

        </div>

        {{-- BUTTON --}}
        <div class="mt-10 flex items-center gap-4">
            <button
                type="submit"
                class="bg-sky-400 hover:bg-sky-500 text-white font-semibold px-8 py-3 rounded-lg shadow transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('operator.tanding.add-petugas') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-lg transition">
                Batal
            </a>
        </div>

    </form>

</div>

@endsection
