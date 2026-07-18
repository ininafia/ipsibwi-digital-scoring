<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dewan IPSI</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-white min-h-screen font-sans">

    {{-- HEADER --}}
    @include('Dewan.penilaian-atlet.header')

    {{-- CONTENT --}}
    <main class="p-4 overflow-x-auto">
        <div class="min-w-[800px]">
            {{-- PESERTA --}}
            @include('Dewan.penilaian-atlet.peserta')

            {{-- SCORE --}}
            @include('Dewan.penilaian-atlet.score-table')

            {{-- PANEL DEWAN --}}
            @include('Dewan.penilaian-atlet.panel-dewan')
        </div>
    </main>

    <script>
        let currentMatchId = '{{ $pertandingan ? $pertandingan->id : "" }}';
        let isActionPending = false;
        let latestDewanFetchId = 0;

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

        function updateDewanUI() {
            if (isActionPending) return;
            let currentFetchId = ++latestDewanFetchId;

            fetch('/dewan/penilaian-atlet/data?_t=' + new Date().getTime())
                .then(res => res.json())
                .then(res => {
                    if (currentFetchId !== latestDewanFetchId) return; // Abaikan respons telat
                    if (isActionPending) return; // Jangan timpa jika sedang ada aksi

                    if (res.success && res.data) {
                        let matchData = res.data.match;
                        let skorData = res.data.data;
                        let displayData = res.data.display;
                        let dewanData = res.data.dewan;

                        currentMatchId = matchData.id || 0;

                        // Update Nama Dewan di Panel
                        if (dewanData) {
                            document.getElementById('dewan-nama-posisi').innerText = dewanData.posisi;
                            document.getElementById('dewan-nama-petugas').innerText = dewanData.nama;
                            document.getElementById('peserta-partai').innerText = matchData.partai || '-';
                        }

                        let timerVal = document.getElementById('timer-value');
                        if(timerVal) {
                            timerVal.innerText = formatTimer(Math.round(matchData.time_remaining || 0));
                        }

                        let currentTimerStatus = matchData.timer_status;
                        if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused')) {
                            showTimerNotification("Waktu Babak Berhenti!");
                        }
                        previousTimerStatus = currentTimerStatus;

                        let currentTimeRemaining = matchData.time_remaining;
                        let currentRound = matchData.round || 1;

                        if (previousRound !== null && currentRound > previousRound) {
                            showTimerNotification("Waktu Babak " + previousRound + " telah habis!");
                        } else if (previousRound !== null && currentRound === 3 && previousTimeRemaining > 0 && currentTimeRemaining === 0) {
                            showTimerNotification("Waktu Pertandingan telah selesai!");
                        }
                        
                        previousRound = currentRound;
                        previousTimeRemaining = currentTimeRemaining;

                        for (let i = 1; i <= 3; i++) {
                            let cellBiru = document.getElementById('dewan-jatuhan-biru-' + i);
                            let cellMerah = document.getElementById('dewan-jatuhan-merah-' + i);
                            let cellBinaanBiru = document.getElementById('dewan-binaan-biru-' + i);
                            let cellBinaanMerah = document.getElementById('dewan-binaan-merah-' + i);
                            let cellHukumanBiru = document.getElementById('dewan-hukuman-biru-' + i);
                            let cellHukumanMerah = document.getElementById('dewan-hukuman-merah-' + i);

                            let roundIndicator = document.getElementById('dewan-round-indicator-' + i);
                            if (roundIndicator) {
                                if (i == currentRound) {
                                    roundIndicator.className = "w-36 bg-[#31b057] text-white font-bold flex items-center justify-center shadow transition-colors duration-300";
                                } else {
                                    roundIndicator.className = "w-36 bg-[#c5c6cc] text-white font-bold flex items-center justify-center shadow transition-colors duration-300";
                                }
                            }

                            const pf = res.data.penalties_formatted[i];
                            if (pf) {
                                if (cellBinaanBiru) cellBinaanBiru.innerText = pf.binaan_biru;
                                if (cellBinaanMerah) cellBinaanMerah.innerText = pf.binaan_merah;
                                if (cellHukumanBiru) cellHukumanBiru.innerText = pf.hukuman_biru;
                                if (cellHukumanMerah) cellHukumanMerah.innerText = pf.hukuman_merah;
                                if (cellBiru) cellBiru.innerText = pf.jatuhan_biru;
                                if (cellMerah) cellMerah.innerText = pf.jatuhan_merah;
                            } else {
                                if (cellBinaanBiru) cellBinaanBiru.innerText = '';
                                if (cellBinaanMerah) cellBinaanMerah.innerText = '';
                                if (cellHukumanBiru) cellHukumanBiru.innerText = '';
                                if (cellHukumanMerah) cellHukumanMerah.innerText = '';
                                if (cellBiru) cellBiru.innerText = '';
                                if (cellMerah) cellMerah.innerText = '';
                            }
                        }

                        // Disable tombol berdasarkan limit maksimalnya saja
                        ['biru', 'merah'].forEach(sudut => {
                            let b = skorData['binaan_' + sudut] || 0;
                            let t = skorData['teguran_' + sudut] || 0;
                            let p = skorData['peringatan_' + sudut] || 0;

                            let btnBinaan = document.getElementById('btn-binaan-' + sudut);
                            let btnTeguran = document.getElementById('btn-teguran-' + sudut);
                            let btnPeringatan = document.getElementById('btn-peringatan-' + sudut);

                            if (btnBinaan) btnBinaan.disabled = (b >= 2);
                            if (btnTeguran) btnTeguran.disabled = (t >= 2);
                            if (btnPeringatan) btnPeringatan.disabled = (p >= 2);
                        });
                    } else if (res.message === 'Tidak ada pertandingan aktif') {
                        if (currentMatchId !== '0' && currentMatchId !== '') {
                            showTimerNotification("Pertandingan Selesai! Mengalihkan ke halaman Petugas...");
                            currentMatchId = ''; 
                            setTimeout(() => {
                                window.location.href = '{{ route("dewan.petugas") }}';
                            }, 3000);
                        }
                    }
                })
                .catch(console.error);
        }

        setInterval(updateDewanUI, 1000);
        updateDewanUI();
    </script>
</body>
</html>
