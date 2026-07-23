<div class="bg-gray-200 rounded-lg p-4 shadow border border-gray-300">
    <div class="flex justify-between items-start gap-4">
        
        <!-- Tombol Sudut Kiri (Biru) -->
        <div class="flex flex-col gap-2 w-[35%]">
            <div class="flex gap-2">
                <button onclick="sendDewanAction('jatuhan', 'biru')" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow active:scale-95 transition-transform">JATUHAN</button>
                <button onclick="sendDewanAction('del-jatuhan', 'biru')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Jatuhan"><i class="fa-solid fa-rotate-left"></i></button>
            </div>
            <div class="flex gap-2">
                <button id="btn-binaan-biru" onclick="sendDewanAction('binaan', 'biru')" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95 transition-transform">BINAAN</button>
                <button onclick="sendDewanAction('del-binaan', 'biru')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Binaan"><i class="fa-solid fa-rotate-left"></i></button>
            </div>
            <div class="flex gap-2">
                <button id="btn-teguran-biru" onclick="sendDewanAction('teguran', 'biru')" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95 transition-transform">TEGURAN</button>
                <button onclick="sendDewanAction('del-teguran', 'biru')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Teguran"><i class="fa-solid fa-rotate-left"></i></button>
            </div>
            <div class="flex gap-2">
                <button id="btn-peringatan-biru" onclick="sendDewanAction('peringatan', 'biru')" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95 transition-transform">PERINGATAN</button>
                <button onclick="sendDewanAction('del-peringatan', 'biru')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Peringatan"><i class="fa-solid fa-rotate-left"></i></button>
            </div>
        </div>

        <!-- Bagian Tengah (Logo & Request) -->
        <div class="flex flex-col items-center justify-between w-[30%] h-full mt-2">
            <div class="flex flex-col items-center">
                <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-[70px] h-[70px] object-contain mb-1">
                <h2 id="dewan-nama-posisi" class="text-xl font-extrabold text-black leading-tight">DEWAN</h2>
                <p id="dewan-nama-petugas" class="text-sm font-bold text-black mb-8 text-center uppercase">MENUNGGU PENUGASAN</p>
            </div>
            
            <button class="bg-[#31b057] hover:bg-green-600 text-white font-bold py-2.5 px-8 rounded shadow w-40 text-[14px] active:scale-95 transition-transform">
                REQUEST ?
            </button>
        </div>

        <!-- Tombol Sudut Kanan (Merah) -->
        <div class="flex flex-col gap-2 w-[35%] items-end">
            <div class="flex gap-2 w-full justify-end">
                <button onclick="sendDewanAction('del-jatuhan', 'merah')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Jatuhan"><i class="fa-solid fa-rotate-left"></i></button>
                <button onclick="sendDewanAction('jatuhan', 'merah')" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow active:scale-95 transition-transform">JATUHAN</button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button onclick="sendDewanAction('del-binaan', 'merah')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Binaan"><i class="fa-solid fa-rotate-left"></i></button>
                <button id="btn-binaan-merah" onclick="sendDewanAction('binaan', 'merah')" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95 transition-transform">BINAAN</button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button onclick="sendDewanAction('del-teguran', 'merah')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Teguran"><i class="fa-solid fa-rotate-left"></i></button>
                <button id="btn-teguran-merah" onclick="sendDewanAction('teguran', 'merah')" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95 transition-transform">TEGURAN</button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button onclick="sendDewanAction('del-peringatan', 'merah')" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded w-12 text-[16px] shadow border border-gray-700 active:scale-95 transition-transform" title="Hapus Peringatan"><i class="fa-solid fa-rotate-left"></i></button>
                <button id="btn-peringatan-merah" onclick="sendDewanAction('peringatan', 'merah')" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95 transition-transform">PERINGATAN</button>
            </div>
        </div>

    </div>
</div>

<script>
    function sendDewanAction(action, sudut) {
        isActionPending = true; // Blokir polling sementara agar UI tidak tertimpa

        // OPTIMISTIC UI UPDATE (Realtime visual feedback)
        let round = (typeof currentRound !== 'undefined') ? currentRound : 1;
        let cell = null;

        if (action === 'jatuhan') {
            cell = document.getElementById('dewan-jatuhan-' + sudut + '-' + round);
            if (cell) cell.innerText = cell.innerText ? cell.innerText + '+3' : '3';
        } else if (action === 'del-jatuhan') {
            cell = document.getElementById('dewan-jatuhan-' + sudut + '-' + round);
            if (cell && cell.innerText.includes('3')) {
                let parts = cell.innerText.split('+');
                parts.pop();
                cell.innerText = parts.join('+');
            }
        } else if (action === 'binaan') {
            cell = document.getElementById('dewan-binaan-' + sudut + '-' + round);
            if (cell) {
                let val = parseInt(cell.innerText) || 0;
                cell.innerText = val + 1;
            }
        } else if (action === 'del-binaan') {
            cell = document.getElementById('dewan-binaan-' + sudut + '-' + round);
            if (cell) {
                let val = parseInt(cell.innerText) || 0;
                cell.innerText = val > 1 ? val - 1 : '';
            }
        } else if (action === 'teguran') {
            cell = document.getElementById('dewan-hukuman-' + sudut + '-' + round);
            if (cell) {
                if (cell.innerText.includes('-1') && !cell.innerText.includes('-2')) {
                    cell.innerText = cell.innerText + '-2';
                } else if (!cell.innerText.includes('-1')) {
                    // Prepend -1 to keep order -1-2-5-10 roughly (though backend will sort it out instantly)
                    cell.innerText = '-1' + cell.innerText;
                }
            }
        } else if (action === 'del-teguran') {
            cell = document.getElementById('dewan-hukuman-' + sudut + '-' + round);
            if (cell) {
                if (cell.innerText.includes('-2')) cell.innerText = cell.innerText.replace('-2', '');
                else if (cell.innerText.includes('-1')) cell.innerText = cell.innerText.replace('-1', '');
            }
        } else if (action === 'peringatan') {
            cell = document.getElementById('dewan-hukuman-' + sudut + '-' + round);
            if (cell) {
                if (cell.innerText.includes('-5') && !cell.innerText.includes('-10')) {
                    cell.innerText = cell.innerText + '-10';
                } else if (!cell.innerText.includes('-5')) {
                    cell.innerText = cell.innerText + '-5';
                }
            }
        } else if (action === 'del-peringatan') {
            cell = document.getElementById('dewan-hukuman-' + sudut + '-' + round);
            if (cell) {
                if (cell.innerText.includes('-10')) cell.innerText = cell.innerText.replace('-10', '');
                else if (cell.innerText.includes('-5')) cell.innerText = cell.innerText.replace('-5', '');
            }
        }

        let url = '';
        if (action === 'jatuhan') url = '/dewan/penilaian-atlet/jatuhan';
        else if (action === 'del-jatuhan') url = '/dewan/penilaian-atlet/del-jatuhan';
        else if (action === 'binaan') url = '/dewan/penilaian-atlet/binaan';
        else if (action === 'del-binaan') url = '/dewan/penilaian-atlet/del-binaan';
        else if (action === 'teguran') url = '/dewan/penilaian-atlet/teguran';
        else if (action === 'del-teguran') url = '/dewan/penilaian-atlet/del-teguran';
        else if (action === 'peringatan') url = '/dewan/penilaian-atlet/peringatan';
        else if (action === 'del-peringatan') url = '/dewan/penilaian-atlet/del-peringatan';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id_pertandingan: currentMatchId,
                id_babak: round,
                sudut: sudut
            })
        })
        .then(response => response.json())
        .then(data => {
            if(!data.success) {
                console.error('Gagal: ' + data.message);
                alert('Gagal: ' + data.message);
            }
            
            isActionPending = false;
            if (typeof updateDewanUI === 'function') updateDewanUI();
        })
        .catch(err => {
            console.error(err);
            isActionPending = false;
            if (typeof updateDewanUI === 'function') updateDewanUI();
        });
    }
</script>
