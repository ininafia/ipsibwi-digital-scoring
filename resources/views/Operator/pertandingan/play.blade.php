@extends('Operator.layout.fullscreen')

@section('title', 'Play Pertandingan')

@section('content')

<div class="min-h-screen bg-gray-100 font-[Poppins]">

    {{-- TOP BAR --}}
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-3 flex items-center justify-between">

        {{-- LOGO --}}
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logos/LOGO IPSI.png') }}"
                 alt="Logo IPSI"
                 class="h-12 w-12 object-contain">
        </div>

        {{-- JUDUL TENGAH --}}
        <div class="flex-1 overflow-hidden" style="visibility: hidden;" id="matchStatusContainer">
            <marquee scrollamount="6" class="text-[22px] font-bold text-gray-900 tracking-wide text-center pt-2">
                Pertandingan Kategori Tanding Sedang Berlangsung...
            </marquee>
        </div>

        {{-- TOMBOL FINALISASI --}}
        <button
            onclick="openFinalisasiModal()"
            class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold text-sm rounded border border-gray-400 transition">
            Finalisasi
        </button>

    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-6 mt-4 shadow-sm" role="alert">
        <span class="block sm:inline font-semibold">{{ session('error') }}</span>
    </div>
    @endif
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-6 mt-4 shadow-sm" role="alert">
        <span class="block sm:inline font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    {{-- KONTEN UTAMA --}}
    <div class="p-6">
        <div class="bg-white shadow-md border border-gray-200 rounded-lg p-8">

            {{-- INFORMASI PERTANDINGAN --}}
            <div class="text-center mb-8">
                <p class="text-[15px] font-semibold text-gray-700 uppercase tracking-wider">
                    PARTAI {{ str_pad($data->partai, 2, '0', STR_PAD_LEFT) }}
                </p>
                <p class="text-[13px] text-gray-500 uppercase">
                    Kelas {{ $data->kelas }}
                    {{ $data->jenis_kelamin == 'putra' ? 'PA' : ($data->jenis_kelamin == 'putri' ? 'PI' : $data->jenis_kelamin) }}
                    {{ $data->golongan }}
                </p>
                <div class="mt-3 flex justify-center items-center gap-3">
                    <span id="displayRound" class="inline-block px-8 py-2 bg-gray-200 rounded-lg text-[18px] font-bold text-gray-800">
                        ROUND 1
                    </span>
                    <span id="displayTimer" class="inline-block px-6 py-2 bg-red-600 text-white rounded-lg text-[18px] font-bold tracking-widest shadow-inner">
                        02:00
                    </span>
                </div>
            </div>

            {{-- AREA ATLET --}}
            <div class="flex items-center justify-center gap-12">

                {{-- SUDUT BIRU --}}
                <div class="w-[300px] rounded-[20px] overflow-hidden bg-white shadow-lg border border-gray-100">
                    <div class="h-[190px] bg-gradient-to-b from-blue-600 to-blue-900 flex items-center justify-center">
                        <span id="displayScoreBlue" class="text-[100px] font-bold text-white leading-none">0</span>
                    </div>
                    <div class="py-4 text-center">
                        <h3 class="text-[15px] font-semibold text-black">
                            {{ $data->sudut_biru ?? 'Belum ada atlet' }}
                        </h3>
                        <p class="text-[16px] font-bold text-blue-700 uppercase">
                            {{ $data->kontingen_biru ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- VS --}}
                <div class="w-[100px] h-[100px] bg-gray-200 rounded-xl shadow-md flex items-center justify-center">
                    <span class="text-[38px] font-bold text-gray-700">VS</span>
                </div>

                {{-- SUDUT MERAH --}}
                <div class="w-[300px] rounded-[20px] overflow-hidden bg-white shadow-lg border border-gray-100">
                    <div class="h-[190px] bg-gradient-to-b from-red-500 to-red-800 flex items-center justify-center">
                        <span id="displayScoreRed" class="text-[100px] font-bold text-white leading-none">0</span>
                    </div>
                    <div class="py-4 text-center">
                        <h3 class="text-[15px] font-semibold text-black">
                            {{ $data->sudut_merah ?? 'Belum ada atlet' }}
                        </h3>
                        <p class="text-[16px] font-bold text-red-600 uppercase">
                            {{ $data->kontingen_merah ?? '-' }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- TOMBOL BAWAH --}}
            <div class="flex justify-between mt-10">
                <a href="{{ route('operator.tanding.waiting-list.index') }}"
                   class="px-6 py-2 bg-sky-400 hover:bg-sky-500 text-white font-bold text-base rounded-md transition">
                    Kembali ke Waiting List
                </a>
                <a href="{{ route('operator.tanding.add-jadwal') }}"
                   class="px-6 py-2 bg-sky-400 hover:bg-sky-500 text-white font-bold text-base rounded-md transition">
                    Kembali ke Input Jadwal
                </a>
            </div>

        </div>
    </div>

</div>

{{-- ============================================================ --}}
{{-- MODAL FINALISASI                                              --}}
{{-- ============================================================ --}}
<div id="modalFinalisasi"
     class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-2xl w-[620px] p-6 relative">

        {{-- JUDUL MODAL --}}
        <h2 class="text-[18px] font-bold text-gray-800 mb-5">Winners Score</h2>

        {{-- TOMBOL TUTUP --}}
        <button
            onclick="document.getElementById('modalFinalisasi').classList.add('hidden')"
            class="absolute top-4 right-5 text-gray-500 hover:text-gray-800 text-xl font-bold leading-none">
            &times;
        </button>

        {{-- ISI MODAL --}}
        <div class="flex items-start gap-6">

            {{-- SCORE PANEL --}}
            <div class="flex items-center gap-3">

                {{-- BIRU --}}
                <div class="text-center">
                    <div class="w-[90px] h-[70px] bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-[36px] font-bold text-white" id="scoreBlue">0</span>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Winner Corner</p>
                    <span class="inline-block mt-1 px-3 py-[2px] bg-blue-600 text-white text-[11px] font-bold rounded">
                        SCORE
                    </span>
                </div>

                {{-- POINT VS --}}
                <div class="text-center px-1">
                    <p class="text-[11px] font-semibold text-gray-600">POINT</p>
                    <p class="text-[14px] font-bold text-gray-700">VS</p>
                </div>

                {{-- MERAH --}}
                <div class="text-center">
                    <div class="w-[90px] h-[70px] bg-red-600 rounded-lg flex items-center justify-center">
                        <span class="text-[36px] font-bold text-white" id="scoreRed">0</span>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Stage Winner</p>
                    <span class="inline-block mt-1 px-3 py-[2px] bg-gray-200 text-gray-700 text-[11px] font-bold rounded">
                        SCORE
                    </span>
                </div>

            </div>

            {{-- FORM DROPDOWN --}}
            <div class="flex-1 space-y-4 mt-1">

                {{-- SUDUT PEMENANG --}}
                <div class="flex items-center gap-3">
                    <label class="text-[14px] font-semibold text-gray-700 w-[140px] shrink-0">
                        Sudut Pemenang
                    </label>
                    <div class="flex-1">
                        <div id="sudutPemenangLabel" class="border border-gray-300 rounded px-3 py-1.5 text-sm font-bold text-gray-800 bg-gray-50 shadow-sm flex items-center gap-2 max-w-[200px] uppercase">
                            <i class="fa-solid fa-trophy text-gray-400 text-xs"></i>
                            <span id="sudutPemenangText">-</span>
                        </div>
                    </div>
                </div>

                {{-- JENIS KEMENANGAN --}}
                <div class="flex items-center gap-3">
                    <label class="text-[14px] font-semibold text-gray-700 w-[140px] shrink-0">
                        Jenis Kemenangan
                    </label>
                    <div class="flex-1">
                        <select id="jenisKemenangan"
                                name="jenis_kemenangan"
                                class="border border-gray-300 rounded px-2 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400 max-w-[200px] w-full bg-white shadow-sm">
                            <option value="">-- Pilih --</option>
                        <option value="angka">Angka</option>
                        <option value="teknik">Teknik</option>
                        <option value="mutlak">Mutlak</option>
                        <option value="wmp">Wmp</option>
                        <option value="disk">Disk</option>
                        <option value="undur_diri">Undur Diri</option>
                    </select>
                    </div>
                </div>

            </div>

        </div>

        {{-- TOMBOL SAVE --}}
        <div class="flex justify-end mt-6">
            <form method="POST" action="{{ route('operator.tanding.finalisasi.store', $data->id) }}">
                @csrf
                <input type="hidden" name="sudut_pemenang"   id="hidSudut">
                <input type="hidden" name="jenis_kemenangan" id="hidJenis">
                <button type="button"
                        onclick="submitFinalisasi()"
                        class="px-6 py-2 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded transition">
                    Save
                </button>
            </form>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    function openFinalisasiModal() {
        fetch('/operator/monitor-display/data?_t=' + new Date().getTime())
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    let sBiru = res.data.skor_biru || 0;
                    let sMerah = res.data.skor_merah || 0;
                    
                    document.getElementById('scoreBlue').innerText = sBiru;
                    document.getElementById('scoreRed').innerText = sMerah;
                    
                    let pemenang = '';
                    let pemenangLabel = '';
                    if (sBiru > sMerah) {
                        pemenang = 'biru';
                        pemenangLabel = 'Biru';
                    } else if (sMerah > sBiru) {
                        pemenang = 'merah';
                        pemenangLabel = 'Merah';
                    } else {
                        pemenang = 'no_win';
                        pemenangLabel = 'Seri / No Win';
                    }
                    
                    document.getElementById('sudutPemenangText').innerText = pemenangLabel;
                    
                    // Set color for label
                    let labelEl = document.getElementById('sudutPemenangLabel');
                    labelEl.classList.remove('text-blue-700', 'text-red-700', 'text-gray-800', 'bg-blue-50', 'bg-red-50', 'bg-gray-50', 'border-blue-300', 'border-red-300');
                    if(pemenang === 'biru') {
                        labelEl.classList.add('text-blue-700', 'bg-blue-50', 'border-blue-300');
                    } else if(pemenang === 'merah') {
                        labelEl.classList.add('text-red-700', 'bg-red-50', 'border-red-300');
                    } else {
                        labelEl.classList.add('text-gray-800', 'bg-gray-50');
                    }

                    document.getElementById('hidSudut').value = pemenang;
                    
                    document.getElementById('modalFinalisasi').classList.remove('hidden');
                } else {
                    alert('Gagal mengambil data skor.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat mengambil skor.');
            });
    }

    function formatTimer(totalSeconds) {
        if (!totalSeconds || totalSeconds < 0) return '00:00';
        const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
        const s = (totalSeconds % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    function showTimerNotification(message) {
        let toast = document.getElementById('timer-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'timer-toast';
            toast.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-8 py-3 rounded-lg shadow-xl font-bold text-xl transition-opacity duration-300 z-[9999] opacity-0 pointer-events-none';
            document.body.appendChild(toast);
        }
        toast.innerText = message;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
        
        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 3000);
    }

    let previousTimerStatus = null;
    let previousTimeRemaining = null;
    let previousRound = null;

    function updateOperatorUI() {
        fetch('/operator/monitor-display/data?_t=' + new Date().getTime())
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    let sBiru = res.data.skor_biru || 0;
                    let sMerah = res.data.skor_merah || 0;
                    
                    document.getElementById('displayScoreBlue').innerText = sBiru;
                    document.getElementById('displayScoreRed').innerText = sMerah;
                    
                    if (res.match && res.match.round) {
                        document.getElementById('displayRound').innerText = 'ROUND ' + res.match.round;

                        // Tampilkan timer
                        document.getElementById('displayTimer').innerText = formatTimer(Math.round(res.match.time_remaining || 0));

                        // Tampilkan teks berjalan jika pertandingan sedang berlangsung (timer jalan)
                        let statusContainer = document.getElementById('matchStatusContainer');
                        if (res.match.timer_status === 'playing') {
                            statusContainer.style.visibility = 'visible';
                        } else {
                            statusContainer.style.visibility = 'hidden';
                        }
                        
                        let currentTimerStatus = res.match.timer_status;
                        if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused')) {
                            showTimerNotification("Waktu Babak Berhenti!");
                        }
                        previousTimerStatus = currentTimerStatus;

                        let currentTimeRemaining = res.match.time_remaining;
                        let currentRound = res.match.round || 1;

                        if (previousRound !== null && currentRound > previousRound) {
                            showTimerNotification("Waktu Babak " + previousRound + " telah habis!");
                        } else if (previousRound !== null && currentRound === 3 && previousTimeRemaining > 0 && currentTimeRemaining === 0) {
                            showTimerNotification("Waktu Pertandingan telah selesai!");
                        }

                        previousRound = currentRound;
                        previousTimeRemaining = currentTimeRemaining;
                    }
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    setInterval(updateOperatorUI, 1000);
    updateOperatorUI();

    function submitFinalisasi() {
        const sudut = document.getElementById('hidSudut').value;
        const jenis = document.getElementById('jenisKemenangan').value;

        if (!sudut || !jenis) {
            alert('Harap pastikan Sudut Pemenang dan Jenis Kemenangan terpilih.');
            return;
        }

        document.getElementById('hidJenis').value = jenis;

        // Submit the parent form
        document.getElementById('hidSudut').closest('form').submit();
    }

    // Close modal when clicking backdrop
    document.getElementById('modalFinalisasi').addEventListener('click', function (e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
@endsection