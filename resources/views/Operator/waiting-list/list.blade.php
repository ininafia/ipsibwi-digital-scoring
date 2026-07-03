@extends('Operator.layout.app')

@section('content')

<div class="bg-white shadow-md min-h-[540px] border border-gray-200 p-8">

    {{-- TAB --}}
    <div class="flex justify-center gap-16 mb-10">

        <button
            class="font-bold text-[16px] border-b-[3px] border-sky-400 pb-3 px-8">
            WAITING LIST
        </button>

        <button
            class="font-bold text-[16px] text-black pb-3">
            FINISHED
        </button>

        <button
            class="font-bold text-[16px] text-black pb-3">
            THE FINAL RESULT
        </button>

    </div>

    {{-- TOP ACTION --}}
    <div class="flex items-center justify-between mb-5">

        {{-- ENTRIES --}}
        <div class="flex items-center gap-3">

            <div
                class="w-[42px] h-[42px] border border-black rounded flex items-center justify-center text-[22px]">
                8
            </div>

            <span class="text-gray-400 text-[16px]">
                Entries per page
            </span>

        </div>

        {{-- SEARCH --}}
        <div class="relative">

            <i
                class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-300">
            </i>

            <input
                type="text"
                placeholder="Search"
                class="w-[190px] h-[40px] rounded-xl border border-black bg-transparent pl-11 pr-4 outline-none italic text-gray-500">

        </div>

    </div>

    {{-- TABLE --}}
    <div class="overflow-hidden border border-black">

        <table class="w-full border-collapse">

            {{-- HEAD --}}
            <thead>

            <tr class="h-[48px]">

                <th class="border border-black w-[55px] text-center text-[14px] font-bold">
                    No
                </th>

                <th class="border border-black w-[130px] text-center text-[14px] font-bold">
                    Partai
                </th>

                <th class="border border-black w-[130px] text-center text-[14px] font-bold">
                    Kelas
                </th>

                <th class="border border-black bg-blue-700 text-white text-center text-[14px] font-bold">
                    Biru
                </th>

                <th class="border border-black bg-red-600 text-white text-center text-[14px] font-bold">
                    Merah
                </th>

                <th class="border border-black bg-gray-300 w-[180px] text-center text-[14px] font-bold">
                    Action
                </th>

            </tr>

            </thead>

            {{-- BODY --}}
            <tbody>

            {{-- ↓↓ UBAH BAGIAN INI: loop dari $list, tombol play pakai $item->id ↓↓ --}}
            @forelse ($list as $no => $item)

                <tr class="h-[66px]">

                    <td class="border border-black text-center">
                        {{ $no + 1 }}
                    </td>

                    <td class="border border-black text-center">
                        <span class="font-medium">
                            {{ $item->partai ?? '-' }}
                        </span>
                    </td>

                    <td class="border border-black text-center">

                        <div class="text-[14px]">
                            {{ $item->kelas ?? '-' }}
                        </div>

                        <div class="inline-block mt-1 {{ $item->jenis_kelamin == 'putra' ? 'bg-blue-400' : 'bg-yellow-400' }} text-black text-[12px] font-semibold px-4 py-[2px] rounded">
                            {{ ucfirst($item->jenis_kelamin ?? '-') }}
                        </div>

                    </td>

                    {{-- BIRU --}}
                    <td class="border border-black text-center">

                        <div class="text-blue-700 font-semibold text-[14px]">
                            {{ $item->sudut_biru ?? '-' }}
                        </div>

                        <div class="text-[14px] font-medium">
                            {{ $item->kontingen_biru ?? '-' }}
                        </div>

                    </td>

                    {{-- MERAH --}}
                    <td class="border border-black text-center">

                        <div class="text-red-500 font-semibold text-[14px]">
                            {{ $item->sudut_merah ?? '-' }}
                        </div>

                        <div class="text-[14px] font-medium">
                            {{ $item->kontingen_merah ?? '-' }}
                        </div>

                    </td>

                    {{-- ACTION --}}
                    <td class="border border-black">

                        <div class="flex items-center justify-center gap-2">

                            {{-- ↓ PLAY: gunakan $item->id, bukan hardcode 1 ↓ --}}
                            <a
                                href="{{ route('operator.pertandingan.play', $item->id) }}"
                                class="w-[42px] h-[30px] border border-green-400 rounded text-green-400 hover:bg-green-50 transition flex items-center justify-center">

                                <i class="fa-regular fa-circle-play"></i>

                            </a>

                            {{-- EDIT --}}
                            <button
                                type="button"
                                onclick="openModal()"
                                class="w-[42px] h-[30px] border border-yellow-400 rounded text-yellow-500 hover:bg-yellow-50 transition">

                                <i class="fa-regular fa-pen-to-square"></i>

                            </button>

                            {{-- DELETE --}}
                            <button
                                type="button"
                                class="w-[42px] h-[30px] border border-red-400 rounded text-red-500 hover:bg-red-50 transition">

                                <i class="fa-regular fa-trash-can"></i>

                            </button>

                        </div>

                    </td>

                </tr>

            @empty

                {{-- Tampil jika $list kosong --}}
                <tr class="h-[65px]">
                    <td colspan="6" class="border border-black text-center text-gray-400 italic">
                        Tidak ada data pertandingan
                    </td>
                </tr>

            @endforelse

            {{-- EMPTY ROW SISA (agar tabel tetap 8 baris) --}}
            @for ($i = count($list); $i < 8; $i++)

                <tr class="h-[65px]">
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                </tr>

            @endfor

            </tbody>

        </table>

    </div>

</div>

{{-- INCLUDE MODAL --}}
@include('waiting-list.update')

@endsection