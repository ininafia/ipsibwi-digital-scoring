<div class="bg-gray-200 rounded-lg p-4 shadow border border-gray-300">
    <div class="flex justify-between items-start gap-4">
        
        <!-- Tombol Sudut Kiri (Biru) -->
        <div class="flex flex-col gap-2 w-[35%]">
            <div class="flex gap-2">
                <button onclick="sendDewanAction('jatuhan', 'biru', this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow transition-all active:scale-95">JATUHAN</button>
                <button onclick="sendDewanAction('del-jatuhan', 'biru', this)" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow border border-gray-700 transition-all active:scale-95">DEL JATUHAN</button>
            </div>
            <div class="flex gap-2">
                <button id="btn-binaan-biru" onclick="sendDewanAction('binaan', 'biru', this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow transition-all active:scale-95">BINAAN</button>
            </div>
            <div class="flex gap-2">
                <button id="btn-teguran-biru" onclick="sendDewanAction('teguran', 'biru', this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow transition-all active:scale-95">TEGURAN</button>
                <button onclick="sendDewanAction('del-hukuman', 'biru', this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow transition-all active:scale-95">DEL HUKUMAN</button>
            </div>
            <div class="flex gap-2">
                <button id="btn-peringatan-biru" onclick="sendDewanAction('peringatan', 'biru', this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow transition-all active:scale-95">PERINGATAN</button>
            </div>
        </div>

        <!-- Bagian Tengah (Logo & Request) -->
        <div class="flex flex-col items-center justify-between w-[30%] h-full mt-2">
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-[70px] h-[70px] object-contain mb-8">
            
            <button class="bg-[#31b057] hover:bg-green-600 text-white font-bold py-2.5 px-8 rounded shadow w-40 text-[14px] transition-all active:scale-95">
                REQUEST ?
            </button>
        </div>

        <!-- Tombol Sudut Kanan (Merah) -->
        <div class="flex flex-col gap-2 w-[35%] items-end">
            <div class="flex gap-2 w-full justify-end">
                <button onclick="sendDewanAction('del-jatuhan', 'merah', this)" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow border border-gray-700 transition-all active:scale-95">DEL JATUHAN</button>
                <button onclick="sendDewanAction('jatuhan', 'merah', this)" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow transition-all active:scale-95">JATUHAN</button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button id="btn-binaan-merah" onclick="sendDewanAction('binaan', 'merah', this)" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow transition-all active:scale-95">BINAAN</button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button onclick="sendDewanAction('del-hukuman', 'merah', this)" class="bg-[#100bd3] hover:bg-blue-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow transition-all active:scale-95">DEL HUKUMAN</button>
                <button id="btn-teguran-merah" onclick="sendDewanAction('teguran', 'merah', this)" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded flex-1 text-[13px] shadow transition-all active:scale-95">TEGURAN</button>
            </div>
            <div class="flex gap-2 w-full justify-end">
                <button id="btn-peringatan-merah" onclick="sendDewanAction('peringatan', 'merah', this)" class="bg-[#cc0000] hover:bg-red-800 text-white font-bold py-3 px-2 rounded w-[calc(50%-0.25rem)] text-[13px] shadow transition-all active:scale-95">PERINGATAN</button>
            </div>
        </div>

    </div>
</div>

<script>
    function sendDewanAction(action, sudut, btn) {
        let originalText = '';
        if(btn) {
            originalText = btn.innerText;
            btn.innerText = '...';
            btn.classList.add('opacity-75', 'scale-95');
        }

        let url = '';
        if (action === 'jatuhan') url = '/dewan/penilaian-atlet/jatuhan';
        else if (action === 'del-jatuhan') url = '/dewan/penilaian-atlet/del-jatuhan';
        else if (action === 'binaan') url = '/dewan/penilaian-atlet/binaan';
        else if (action === 'del-hukuman') url = '/dewan/penilaian-atlet/del-hukuman';
        else if (action === 'teguran') url = '/dewan/penilaian-atlet/teguran';
        else if (action === 'peringatan') url = '/dewan/penilaian-atlet/peringatan';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id_pertandingan: {{ $pertandingan->id ?? 0 }},
                sudut: sudut
            })
        })
        .then(response => response.json())
        .then(data => {
            if(!data.success) {
                console.error('Gagal: ' + data.message);
                alert('Gagal: ' + data.message);
            }
            if(btn) {
                btn.innerText = originalText;
                btn.classList.remove('opacity-75', 'scale-95');
            }
        })
        .catch(err => {
            console.error(err);
            if(btn) {
                btn.innerText = originalText;
                btn.classList.remove('opacity-75', 'scale-95');
            }
        });
    }
</script>
