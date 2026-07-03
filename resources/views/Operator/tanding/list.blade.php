@extends('Operator.layout.app')

@section('content')

<div class="bg-white shadow-md border border-gray-200 p-6 rounded-xl">

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- TAB --}}
    <div class="flex justify-center gap-16 border-b border-gray-200 mb-6">

        <button
            id="tab-waiting"
            onclick="switchTab('waiting')"
            class="tab-btn {{ $tab == 'waiting' ? 'border-sky-400 text-sky-500' : 'border-transparent text-gray-700' }} font-bold text-[15px] pb-3 px-4 border-b-[3px]">
            WAITING LIST
        </button>

        <button
            id="tab-finished"
            onclick="switchTab('finished')"
            class="tab-btn {{ $tab == 'finished' ? 'border-sky-400 text-sky-500' : 'border-transparent text-gray-700' }} font-bold text-[15px] pb-3 px-4 border-b-[3px]">
            FINISHED
        </button>

        <button
            id="tab-final"
            onclick="switchTab('final')"
            class="tab-btn {{ $tab == 'final' ? 'border-sky-400 text-sky-500' : 'border-transparent text-gray-700' }} font-bold text-[15px] pb-3 px-4 border-b-[3px]">
            THE FINAL RESULT
        </button>

    </div>

    {{-- TOOLBAR --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">

        {{-- Entries --}}
        <div class="flex items-center gap-2">

            <select
                id="entriesPerPage"
                onchange="changeEntries(this.value)"
                class="border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-300">

                <option value="8">8</option>
                <option value="16">16</option>
                <option value="32">32</option>

            </select>

            <span class="text-sm text-gray-500">
                Entries per page
            </span>

        </div>

        {{-- Search --}}
        <div class="relative">

            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24">

                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />

                </svg>

            </span>

            <input
                type="text"
                id="searchInput"
                onkeyup="filterTable()"
                placeholder="Search"
                class="border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 w-64">

        </div>

    </div>

    {{-- TABLE --}}
    @if($tab === 'finished')
        @include('Operator.finished.list')
    @elseif($tab === 'final')
        <div class="p-8 mt-4 text-center text-gray-500 italic border border-gray-200 rounded-lg">
            Halaman The Final Result belum tersedia.
        </div>
    @else
        <div class="overflow-x-auto">
            <table
                class="w-full border border-gray-300 text-sm"
                id="jadwalTable">

                <thead>

                    <tr>

                        <th class="border border-gray-300 px-4 py-3 bg-gray-100 text-center font-bold w-16">
                            No
                        </th>

                        <th class="border border-gray-300 px-4 py-3 bg-gray-100 text-center font-bold w-24">
                            Partai
                        </th>

                        <th class="border border-gray-300 px-4 py-3 bg-gray-100 text-center font-bold">
                            Kelas
                        </th>

                        <th class="border border-gray-300 px-4 py-3 bg-blue-600 text-white text-center font-bold">
                            Biru
                        </th>

                        <th class="border border-gray-300 px-4 py-3 bg-red-600 text-white text-center font-bold">
                            Merah
                        </th>

                        <th class="border border-gray-300 px-4 py-3 bg-gray-100 text-center font-bold w-40">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody id="tableBody">

                    @forelse($list as $item)

                    <tr class="table-row border-b border-gray-200 hover:bg-gray-50">

                        {{-- NO --}}
                        <td class="border border-gray-300 px-4 py-4 text-center font-medium">
                            {{ $loop->iteration }}
                        </td>

                        {{-- PARTAI --}}
                        <td class="border border-gray-300 px-4 py-4 text-center font-medium relative">
                            {{ str_pad($item->partai, 3, '0', STR_PAD_LEFT) }}
                            @if(isset($item->status) && $item->status === 'playing')
                                <div class="absolute -top-2 -right-2 bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                                    LIVE
                                </div>
                            @endif
                        </td>

                        {{-- KELAS --}}
                        <td class="border border-gray-300 px-4 py-4 text-center">

                            <div class="font-semibold text-gray-800">
                                {{ strtoupper($item->gelanggang ?? '-') }}
                                |
                                {{ strtoupper($item->kelas ?? '-') }}
                            </div>

                            <span class="inline-block mt-1 bg-yellow-400 text-white text-xs font-bold px-3 py-0.5 rounded">
                                {{ ucfirst($item->jenis_kelamin ?? '-') }}
                            </span>

                        </td>

                        {{-- BIRU --}}
                        <td class="border border-gray-300 px-4 py-4 text-center">

                            @if(!empty($item->sudut_biru))

                            <div class="font-semibold text-blue-600">
                                {{ $item->sudut_biru }}
                            </div>

                            <div class="text-gray-500 text-xs mt-1">
                                {{ $item->kontingen_biru ?? '-' }}
                            </div>

                            @else

                            <span class="text-gray-400 italic">
                                Belum ada atlet
                            </span>

                            @endif

                        </td>

                        {{-- MERAH --}}
                        <td class="border border-gray-300 px-4 py-4 text-center">

                            @if(!empty($item->sudut_merah))

                            <div class="font-semibold text-red-600">
                                {{ $item->sudut_merah }}
                            </div>

                            <div class="text-gray-500 text-xs mt-1">
                                {{ $item->kontingen_merah ?? '-' }}
                            </div>

                            @else

                            <span class="text-gray-400 italic">
                                Belum ada atlet
                            </span>

                            @endif

                        </td>

                        {{-- ACTION --}}
                        <td class="border border-gray-300 px-4 py-4">

                            <div class="flex items-center justify-center gap-2">

                                {{-- PLAY/RESUME --}}
                                @if(isset($item->status) && $item->status === 'playing')
                                    <a
                                        href="{{ route('operator.pertandingan.play', $item->id) }}"
                                        title="Lanjutkan Pertandingan"
                                        class="w-9 h-9 flex items-center justify-center border-2 border-orange-500 bg-orange-500 rounded text-white hover:bg-orange-600 hover:border-orange-600 shadow-sm transition">
                                        <i class="fa-solid fa-play"></i>
                                    </a>
                                @else
                                    <button
                                        onclick="startMatch(this)"
                                        data-id="{{ $item->id }}"
                                        title="Mulai Pertandingan"
                                        class="w-9 h-9 flex items-center justify-center border-2 border-green-500 rounded text-green-500 hover:bg-green-500 hover:text-white transition">

                                        <svg
                                            class="w-4 h-4"
                                            fill="currentColor"
                                            viewBox="0 0 24 24">

                                            <path d="M8 5v14l11-7z" />

                                        </svg>

                                    </button>
                                @endif

                                {{-- EDIT — Trigger Modal --}}
                                <button
                                    onclick="openUpdateModal({{ $item->id }})"
                                    title="Edit"
                                    class="w-9 h-9 flex items-center justify-center border-2 border-sky-400 rounded text-sky-400 hover:bg-sky-400 hover:text-white transition">

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24">

                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />

                                    </svg>

                                </button>

                                {{-- DELETE --}}
                                <button
                                    onclick="confirmDelete(this)"
                                    data-id="{{ $item->id }}"
                                    title="Hapus"
                                    class="w-9 h-9 flex items-center justify-center border-2 border-red-400 rounded text-red-400 hover:bg-red-400 hover:text-white transition">

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24">

                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                        <path d="M9 6V4h6v2" />

                                    </svg>

                                </button>

                            </div>

                        </td>

                    </tr>

                    @empty

                    {{-- EMPTY --}}
                    <tr>

                        <td
                            colspan="6"
                            class="border border-gray-300 py-10 text-center text-gray-400 italic">

                            Data pertandingan belum tersedia

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    @endif

    {{-- PAGINATION --}}
    @if(is_object($list) && method_exists($list, 'links'))
    <div class="mt-5">
        {{ $list->links() }}
    </div>
    @endif

</div>

<script>
    function switchTab(tab) {
        window.location.href = `?tab=${tab}`;
    }

    function filterTable() {
        let input = document
            .getElementById('searchInput')
            .value
            .toLowerCase();

        let rows = document.querySelectorAll('.table-row');

        rows.forEach(row => {

            let text = row.innerText.toLowerCase();

            row.style.display =
                text.includes(input) ?
                '' :
                'none';
        });
    }

    function changeEntries(value) {
        console.log('Entries:', value);
    }

    function startMatch(button) {
        const id = button.dataset.id;

        fetch(`/operator/tanding/waiting-list/${id}/status`, {

                method: 'PATCH',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },

                body: JSON.stringify({
                    status: 'playing'
                })

            })
            .then(response => response.json())
            .then(result => {

                if (result.success) {

                    window.location.href = `/operator/pertandingan/${id}/play`;

                } else {

                    alert(result.message);
                }

            })
            .catch(error => {

                console.error(error);

                alert('Terjadi kesalahan.');

            });
    }

    function confirmDelete(button) {
        const id = button.dataset.id;

        if (confirm('Yakin ingin menghapus data ini?')) {

            fetch(`/operator/tanding/waiting-list/${id}/delete`, {

                    method: 'DELETE',

                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }

                })
                .then(response => response.json())
                .then(result => {

                    if (result.success) {

                        location.reload();

                    } else {

                        alert(result.message);
                    }

                })
                .catch(error => {

                    console.error(error);

                    alert('Terjadi kesalahan.');

                });
        }
    }
    function openUpdateModal(id) {
        fetch(`/operator/tanding/waiting-list/${id}/edit-data`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                
                // Populate the modal fields
                document.getElementById('modal_form').action = `/operator/tanding/waiting-list/${data.id}/update`;
                document.getElementById('modal_nomor').value = data.nomor || '';
                document.getElementById('modal_partai').value = data.partai || '';
                
                // Set selects
                if(data.gelanggang) document.getElementById('modal_gelanggang').value = data.gelanggang;
                if(data.kelas) {
                    // Try exact match first, then uppercase, then lowercase
                    let el = document.getElementById('modal_kelas');
                    el.value = data.kelas;
                    if(!el.value) el.value = data.kelas.toUpperCase();
                    if(!el.value) el.value = data.kelas.toLowerCase();
                }
                if(data.golongan) {
                    let el = document.getElementById('modal_golongan');
                    el.value = data.golongan;
                    if(!el.value) el.value = data.golongan.toLowerCase();
                }
                if(data.jenis_kelamin) {
                    let el = document.getElementById('modal_jenis_kelamin');
                    el.value = data.jenis_kelamin;
                    if(!el.value) el.value = data.jenis_kelamin.toLowerCase();
                }
                
                // Set participants
                document.getElementById('modal_sudut_biru').value = data.sudut_biru || '';
                document.getElementById('modal_kontingen_biru').value = data.kontingen_biru || '';
                document.getElementById('modal_sudut_merah').value = data.sudut_merah || '';
                document.getElementById('modal_kontingen_merah').value = data.kontingen_merah || '';
                
                openModal();
            } else {
                alert(result.message || 'Gagal mengambil data');
            }
        })
        .catch(error => {
            console.error(error);
            alert('Terjadi kesalahan saat mengambil data.');
        });
    }
</script>

@include('Operator.waiting-list.update')

@endsection