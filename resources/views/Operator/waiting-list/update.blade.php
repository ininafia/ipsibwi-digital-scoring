{{-- MODAL UPDATE --}}
<div
    id="updateModal"
    onclick="closeModal()"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/30">

    {{-- BOX --}}
    <div
        onclick="event.stopPropagation()"
        class="relative w-[650px] rounded-lg bg-white shadow-2xl px-8 py-6">

        {{-- CLOSE --}}
        <button
            type="button"
            onclick="closeModal()"
            class="absolute right-5 top-4 text-[24px] text-black hover:text-red-500 transition leading-none">

            &times;

        </button>

        {{-- TITLE --}}
        <h2 class="text-[18px] font-bold mb-5 text-black">
            Update Data Jadwal
        </h2>

        {{-- FORM --}}
        <form id="modal_form" action="" method="POST">

            @csrf
            @method('PUT')
            
            <input type="hidden" name="nomor" id="modal_nomor">

            {{-- TOP --}}
            <div class="flex items-center justify-start gap-2 mb-5">

                {{-- GELANGGANG --}}
                <select
                    name="gelanggang"
                    id="modal_gelanggang"
                    class="w-[55px] h-[36px] border border-gray-400 rounded-md px-2 text-[13px] font-medium text-black outline-none focus:border-black focus:ring-1 focus:ring-black">

                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                    <option value="F">F</option>
                    <option value="G">G</option>

                </select>

                {{-- PARTAI --}}
                <input
                    type="text"
                    name="partai"
                    id="modal_partai"
                    class="w-[55px] h-[36px] border border-gray-400 rounded-md text-center text-[13px] font-medium text-black outline-none focus:border-black focus:ring-1 focus:ring-black">

                {{-- KELAS --}}
                <select
                    name="kelas"
                    id="modal_kelas"
                    class="w-[100px] h-[36px] border border-gray-400 rounded-md px-2 text-[13px] font-medium text-black outline-none focus:border-black focus:ring-1 focus:ring-black">

                    @foreach(['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','bebas','open','open-1','open-2'] as $k)
                        <option value="{{ $k }}">{{ strtoupper($k) }}</option>
                    @endforeach

                </select>

                {{-- USIA / GOLONGAN --}}
                <select
                    name="golongan"
                    id="modal_golongan"
                    class="w-[120px] h-[36px] border border-gray-400 rounded-md px-2 text-[13px] font-medium text-black outline-none focus:border-black focus:ring-1 focus:ring-black">

                    @foreach(['pra usia dini', 'usia dini 1', 'usia dini 2', 'pra remaja', 'remaja', 'dewasa'] as $g)
                        <option value="{{ $g }}">{{ strtoupper($g) }}</option>
                    @endforeach

                </select>

                {{-- JK --}}
                <select
                    name="jenis_kelamin"
                    id="modal_jenis_kelamin"
                    class="w-[120px] h-[36px] border border-gray-400 rounded-md px-2 text-[13px] font-medium text-black outline-none focus:border-black focus:ring-1 focus:ring-black">

                    <option value="putra">PUTRA</option>
                    <option value="putri">PUTRI</option>

                </select>

            </div>

            {{-- PESERTA --}}
            <div class="grid grid-cols-2 gap-3 mb-5">

                {{-- KIRI --}}
                <div>

                    <input
                        type="text"
                        name="sudut_biru"
                        id="modal_sudut_biru"
                        placeholder="Nama Sudut Biru"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full bg-[#0000e6] text-white rounded-md px-4 py-2.5 text-[13px] font-medium mb-1.5 outline-none placeholder-blue-200">

                    <input
                        type="text"
                        name="sudut_merah"
                        id="modal_sudut_merah"
                        placeholder="Nama Sudut Merah"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full bg-[#cc0000] text-white rounded-md px-4 py-2.5 text-[13px] font-medium outline-none placeholder-red-200">

                </div>

                {{-- KANAN --}}
                <div>

                    <input
                        type="text"
                        name="kontingen_biru"
                        id="modal_kontingen_biru"
                        placeholder="Kontingen Biru"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full bg-[#0000e6] text-white rounded-md px-4 py-2.5 text-[13px] font-medium mb-1.5 outline-none placeholder-blue-200">

                    <input
                        type="text"
                        name="kontingen_merah"
                        id="modal_kontingen_merah"
                        placeholder="Kontingen Merah"
                        oninput="this.value = this.value.replace(/\b\w/g, l => l.toUpperCase())"
                        class="w-full bg-[#cc0000] text-white rounded-md px-4 py-2.5 text-[13px] font-medium outline-none placeholder-red-200">

                </div>

            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end">

                <button
                    type="submit"
                    class="bg-[#0000e6] hover:bg-blue-800 transition text-white font-bold text-[13px] px-8 py-2 rounded-md">

                    SAVE

                </button>

            </div>

        </form>

    </div>

</div>

{{-- SCRIPT --}}
<script>

    function openModal() {

        document
            .getElementById('updateModal')
            .classList
            .remove('hidden');

        document
            .getElementById('updateModal')
            .classList
            .add('flex');

    }

    function closeModal() {

        document
            .getElementById('updateModal')
            .classList
            .remove('flex');

        document
            .getElementById('updateModal')
            .classList
            .add('hidden');

    }

    document.getElementById('modal_form').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const url = form.action;
        const formData = new FormData(form);

        fetch(url, {
            method: 'POST', // The form has _method=PUT inside it
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            if (!response.ok && response.status === 422) {
                // Laravel validation error
                const errData = await response.json();
                throw new Error(errData.message || 'Data tidak valid.');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success === false) {
                alert(data.message || 'Gagal menyimpan data');
            } else {
                // Sukses, muat ulang halaman agar tabel ter-update
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan pada server: ' + error.message);
        });
    });

</script>