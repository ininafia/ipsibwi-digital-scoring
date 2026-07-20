@extends('Operator.layout.app')

@section('content')

<div class="bg-white p-6 rounded-xl shadow border border-gray-200">

    {{-- TITLE --}}
    <h1 class="text-2xl font-bold text-sky-400 mb-6">
        Input Jadwal Pertandingan
    </h1>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))

    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms  class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
        {{ session('error') }}
    </div>

    @endif

    {{-- VALIDATION ERROR --}}
    @if($errors->any())

    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms  class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">

        <ul class="list-disc pl-5 space-y-1">

            @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

    @endif

    {{-- SUCCESS --}}
    @if(session('success'))

    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms  class="mb-5 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>

    @endif

    {{-- FORM --}}
    <form action="{{ route('operator.tanding.do-create') }}" method="POST">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- ========================= --}}
            {{-- LEFT --}}
            {{-- ========================= --}}
            <div class="space-y-3">

                {{-- NOMOR --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Nomor
                    </label>

                    <input
                        type="number"
                        name="nomor"
                        value="{{ old('nomor') }}"
                        placeholder="Masukkan nomor"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                </div>

                {{-- PARTAI --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Partai
                    </label>

                    <input
                        type="text"
                        name="partai"
                        value="{{ old('partai') }}"
                        placeholder="Contoh: 001"
                        maxlength="3"
                        pattern="\d{3}"
                        title="Format 3 digit angka, contoh: 001"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">
                </div>

                {{-- GELANGGANG --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Gelanggang
                    </label>

                    <select
                        name="gelanggang"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                        <option value="">
                            Pilih Gelanggang
                        </option>

                        @foreach(['A','B','C','D','E','F','G'] as $g)

                        <option
                            value="{{ $g }}"
                            {{ old('gelanggang') == $g ? 'selected' : '' }}>

                            {{ $g }}

                        </option>

                        @endforeach

                    </select>

                </div>

                {{-- KELAS --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Kelas
                    </label>

                    <select
                        name="kelas"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                        <option value="">
                            Pilih Kelas
                        </option>

                        @foreach([
                        'A','B','C','D','E','F','G',
                        'H','I','J','K','L','M',
                        'N','O','P','Q','R','S',
                        'bebas','open','open-1','open-2'
                        ] as $k)

                        <option
                            value="{{ $k }}"
                            {{ old('kelas') == $k ? 'selected' : '' }}>

                            {{ strtoupper($k) }}

                        </option>

                        @endforeach

                    </select>

                </div>

                {{-- GOLONGAN --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Golongan
                    </label>

                    <select
                        name="golongan"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                        <option value="">
                            Pilih Golongan
                        </option>

                        @foreach([
                        'pra usia dini',
                        'usia dini 1',
                        'usia dini 2',
                        'pra remaja',
                        'remaja',
                        'dewasa'
                        ] as $g)

                        <option
                            value="{{ $g }}"
                            {{ old('golongan') == $g ? 'selected' : '' }}>

                            {{ ucfirst($g) }}

                        </option>

                        @endforeach

                    </select>

                </div>

            </div>

            {{-- ========================= --}}
            {{-- RIGHT --}}
            {{-- ========================= --}}
            <div class="space-y-3">

                {{-- JENIS KELAMIN --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Jenis Kelamin
                    </label>

                    <select
                        name="jenis_kelamin"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                        <option value="">
                            Pilih Jenis Kelamin
                        </option>

                        <option
                            value="putra"
                            {{ old('jenis_kelamin') == 'putra' ? 'selected' : '' }}>

                            Putra

                        </option>

                        <option
                            value="putri"
                            {{ old('jenis_kelamin') == 'putri' ? 'selected' : '' }}>

                            Putri

                        </option>

                    </select>

                </div>

                {{-- SUDUT BIRU --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Sudut Biru
                    </label>

                    <input
                        type="text"
                        name="sudut_biru"
                        value="{{ old('sudut_biru') }}"
                        placeholder="Masukkan nama atlet biru"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                </div>

                {{-- KONTINGEN BIRU --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Kontingen Biru
                    </label>

                    <input
                        type="text"
                        name="kontingen_biru"
                        value="{{ old('kontingen_biru') }}"
                        placeholder="Masukkan kontingen biru"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                </div>

                {{-- SUDUT MERAH --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Sudut Merah
                    </label>

                    <input
                        type="text"
                        name="sudut_merah"
                        value="{{ old('sudut_merah') }}"
                        placeholder="Masukkan nama atlet merah"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                </div>

                {{-- KONTINGEN MERAH --}}
                <div>

                    <label class="block text-sky-500 font-semibold text-sm mb-1">
                        Kontingen Merah
                    </label>

                    <input
                        type="text"
                        name="kontingen_merah"
                        value="{{ old('kontingen_merah') }}"
                        placeholder="Masukkan kontingen merah"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-300 outline-none">

                </div>

            </div>

        </div>

        {{-- BUTTON --}}
        <div class="flex justify-end mt-8">

            <button
                type="submit"
                class="bg-sky-400 hover:bg-sky-500 text-white font-semibold px-6 py-2 text-sm rounded-lg shadow transition">

                Save

            </button>

        </div>

    </form>

</div>

@endsection