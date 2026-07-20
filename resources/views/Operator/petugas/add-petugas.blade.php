@extends('Operator.layout.app')

@section('content')

<div class="bg-white p-6 rounded-xl shadow border border-gray-200">

    {{-- TITLE --}}
    <h1 class="text-2xl font-bold text-sky-400 mb-6">
        Input Data Petugas
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
    <form action="{{ route('operator.petugas.store') }}" method="POST">
        @csrf
        <div class="max-w-xl space-y-6">

            {{-- NAMA PETUGAS --}}
            <div>
                <label class="block text-sky-500 font-semibold text-sm mb-2">
                    Nama
                </label>
                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama') }}"
                    placeholder="Masukkan nama"
                    oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-sky-300 outline-none">
            </div>

            {{-- TUGAS --}}
            <div>
                <label class="block text-sky-500 font-semibold text-sm mb-2">
                    Tugas
                </label>
                <select
                    name="tugas"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-sky-300 outline-none">
                    
                    <option value="">
                        Pilih tugas
                    </option>

                    @foreach([
                        'Ketua Pertandingan',
                        'Delegasi Teknik',
                        'Dewan',
                        'Wasit',
                        'Juri'
                    ] as $tugas)
                    <option
                        value="{{ $tugas }}"
                        {{ old('tugas') == $tugas ? 'selected' : '' }}>
                        {{ $tugas }}
                    </option>
                    @endforeach

                </select>
            </div>

        </div>

        {{-- BUTTON --}}
        <div class="mt-10">
            <button
                type="submit"
                class="bg-sky-400 hover:bg-sky-500 text-white font-semibold px-10 py-3 rounded-lg shadow transition">
                Save
            </button>
        </div>

    </form>

</div>

@endsection
