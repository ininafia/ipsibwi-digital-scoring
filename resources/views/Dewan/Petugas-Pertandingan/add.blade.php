@extends('Dewan.Layout.app')

@section('title', 'Penugasan Petugas')

@section('sidebar')
    @include('Dewan.Layout.sidebar-petugas')
@endsection

@section('content')
    <div class="relative mb-6">
        <h1 class="text-[22px] font-bold text-[#62cbf5] leading-none">Penugasan Petugas</h1>
        <a href="{{ route('dewan.petugas') }}" class="absolute right-0 top-0 -mt-4 text-gray-500 hover:text-[#62cbf5] font-semibold flex items-center gap-2 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    {{-- ERROR MESSAGE --}}
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms  class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms  class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms  class="mb-5 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-md text-sm">
        {{ session('success') }}
    </div>
    @endif

    @php
        // Filter petugas berdasarkan role (tugas)
        $ketuaList    = array_filter($petugasList, fn($p) => $p->tugas === 'Ketua Pertandingan');
        $dewanList    = array_filter($petugasList, fn($p) => $p->tugas === 'Dewan');
        $wasitList    = array_filter($petugasList, fn($p) => $p->tugas === 'Wasit');
        $juriList     = array_filter($petugasList, fn($p) => $p->tugas === 'Juri');
        $delegasiList = array_filter($petugasList, fn($p) => $p->tugas === 'Delegasi Teknik');
    @endphp

    <div class="bg-white rounded-md shadow-sm p-8 max-w-6xl mb-4">
        
        <form action="{{ route('dewan.petugas.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-2 gap-x-12 gap-y-6">
                
                <!-- KOLOM KIRI -->
                <div class="flex flex-col gap-4">
                    
                    <!-- Partai & Gelanggang -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Pertandingan (Partai & Gelanggang)</label>
                        <div class="relative">
                            <select name="id_pertandingan" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Pertandingan</option>
                                @foreach($pertandinganList as $match)
                                <option value="{{ $match->id }}" {{ old('id_pertandingan') == $match->id ? 'selected' : '' }}>
                                    Partai {{ $match->partai }} - Gelanggang {{ $match->gelanggang }}
                                </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Delegasi Teknik -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Delegasi Teknik</label>
                        <div class="relative">
                            <select name="delegasi_teknik" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Delegasi Teknik</option>
                                @foreach($delegasiList as $p)
                                <option value="{{ $p->id }}" {{ old('delegasi_teknik') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Ketua Pertandingan -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Ketua Pertandingan</label>
                        <div class="relative">
                            <select name="ketua" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Ketua Pertandingan</option>
                                @foreach($ketuaList as $p)
                                <option value="{{ $p->id }}" {{ old('ketua') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Dewan -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Dewan</label>
                        <div class="relative">
                            <select name="dewan" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Dewan</option>
                                @foreach($dewanList as $p)
                                <option value="{{ $p->id }}" {{ old('dewan') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN -->
                <div class="flex flex-col gap-4">
                    
                    <!-- Wasit -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Wasit</label>
                        <div class="relative">
                            <select name="wasit" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Wasit</option>
                                @foreach($wasitList as $p)
                                <option value="{{ $p->id }}" {{ old('wasit') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Juri 1 -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Juri 1</label>
                        <div class="relative">
                            <select name="juri1" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Juri 1</option>
                                @foreach($juriList as $p)
                                <option value="{{ $p->id }}" {{ old('juri1') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Juri 2 -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Juri 2</label>
                        <div class="relative">
                            <select name="juri2" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Juri 2</option>
                                @foreach($juriList as $p)
                                <option value="{{ $p->id }}" {{ old('juri2') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Juri 3 -->
                    <div>
                        <label class="block text-[#62cbf5] font-bold text-sm mb-2">Juri 3</label>
                        <div class="relative">
                            <select name="juri3" class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#62cbf5]">
                                <option value="">Pilih Juri 3</option>
                                @foreach($juriList as $p)
                                <option value="{{ $p->id }}" {{ old('juri3') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Tombol Simpan -->
            <div class="mt-4">
                <button type="submit" class="bg-[#82c6ef] hover:bg-[#62cbf5] text-white font-bold py-2 px-6 rounded-md transition shadow-sm w-36 text-sm">
                    Simpan
                </button>
            </div>
        </form>

    </div>
@endsection
