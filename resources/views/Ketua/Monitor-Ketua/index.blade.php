<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Ketua</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    @include('Ketua.Monitor-Ketua.header')

    <!-- Main Content -->
    <main class="flex-1 p-2 sm:p-4 lg:p-6 flex flex-col">
        <div class="bg-white flex-1 shadow-md border border-gray-200 flex flex-col p-2 sm:p-4 lg:p-6">
            
            <!-- Peserta -->
            @include('Ketua.Monitor-Ketua.peserta')

            <!-- Grid Content (Always side-by-side) -->
            <div class="grid grid-cols-5 gap-2 sm:gap-3 lg:gap-4 flex-1 items-start">
                
                <!-- Table Kiri -->
                <div class="col-span-4 w-full overflow-hidden">
                    @include('Ketua.Monitor-Ketua.score-table')
                </div>

                <!-- Panel Kanan -->
                <div class="col-span-1 w-full">
                    @include('Ketua.Monitor-Ketua.right-panel')
                </div>

            </div>

        </div>
    </main>

    <script>
        function setText(id, val) {
            const el = document.getElementById(id);
            if (el) el.innerText = val ?? '';
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
        let currentMatchId = null;
        let currentMatchStatus = null;
        let subscribedMatchId = null; // Lacak kanal yang sudah disubscribe

        let localTimeRemaining = 0;
        let localTimerStatus = 'stopped';
        let localTimerInterval = null;

        // Selalu reset interval dari waktu server agar tidak drift
        let targetEndTime = null;

        function syncLocalTimer(serverTime, timerStatus) {
            localTimeRemaining = serverTime;
            localTimerStatus = timerStatus;

            if (localTimerInterval) {
                clearInterval(localTimerInterval);
                localTimerInterval = null;
            }

            if (timerStatus === 'playing' && localTimeRemaining > 0) {
                targetEndTime = Date.now() + (localTimeRemaining * 1000);
                localTimerInterval = setInterval(() => {
                    let newTime = Math.ceil((targetEndTime - Date.now()) / 1000);
                    if (newTime < 0) newTime = 0;
                    if (newTime !== localTimeRemaining) {
                        localTimeRemaining = newTime;
                        setText('timer-value', formatTimer(localTimeRemaining));
                        
                        if (localTimeRemaining <= 0) {
                            clearInterval(localTimerInterval);
                            localTimerInterval = null;
                        }
                    }
                }, 200);
            }

            setText('timer-value', formatTimer(localTimeRemaining));
        }

        // Warna-warni yang sangat berbeda satu sama lain untuk membedakan nilai sah
        const sahColorPalette = [
            { bg: '#e74c3c', text: '#fff' },  // merah terang
            { bg: '#2ecc71', text: '#fff' },  // hijau
            { bg: '#3498db', text: '#fff' },  // biru
            { bg: '#f39c12', text: '#000' },  // kuning/oranye
            { bg: '#9b59b6', text: '#fff' },  // ungu
            { bg: '#1abc9c', text: '#fff' },  // teal
            { bg: '#e91e63', text: '#fff' },  // pink
            { bg: '#ff6f00', text: '#fff' },  // oranye tua
            { bg: '#00bcd4', text: '#fff' },  // cyan
            { bg: '#8bc34a', text: '#000' },  // hijau muda
            { bg: '#795548', text: '#fff' },  // cokelat
            { bg: '#607d8b', text: '#fff' },  // abu-biru
        ];

        // Map untuk menyimpan warna per window_id / award_id secara berurutan
        const colorAssignment = {};
        let colorIndex = 0;

        function getAssignedColor(id) {
            if (!id) return sahColorPalette[0];
            if (!colorAssignment[id]) {
                colorAssignment[id] = sahColorPalette[colorIndex % sahColorPalette.length];
                colorIndex++;
            }
            return colorAssignment[id];
        }

        function renderEventBoxes(cellId, eventHistory, juriPos, round, athlete) {
            const cell = document.getElementById(cellId);
            if (!cell) return;

            if (!eventHistory || !eventHistory[juriPos] || !eventHistory[juriPos][round]) {
                cell.innerHTML = '';
                return;
            }

            const events = eventHistory[juriPos][round][athlete] || [];
            if (events.length === 0) {
                cell.innerHTML = '';
                return;
            }

            let html = '<div class="evt-container">';
            events.forEach(evt => {
                if (evt.sah) {
                    const color = evt.window_id ? getAssignedColor(evt.window_id) : { bg: (athlete === 'blue' ? '#0000cc' : '#cc0000'), text: '#fff' };
                    html += `<span class="evt-box" style="background:${color.bg};color:${color.text}">${evt.value}</span>`;
                } else {
                    html += `<span class="evt-box evt-tidak-sah">${evt.value}</span>`;
                }
            });
            html += '</div>';
            cell.innerHTML = html;
        }

        function renderAwardBoxes(cellId, awardHistory, round, athlete) {
            const cell = document.getElementById(cellId);
            if (!cell) return;

            if (!awardHistory || !awardHistory[round] || !awardHistory[round][athlete]) {
                cell.innerHTML = '';
                return;
            }

            const events = awardHistory[round][athlete];
            if (events.length === 0) {
                cell.innerHTML = '';
                return;
            }

            let html = '<div class="evt-container">';
            events.forEach(evt => {
                const colorKey = evt.window_id || evt.award_id;
                const color = colorKey ? getAssignedColor(colorKey) : { bg: (athlete === 'blue' ? '#0000cc' : '#cc0000'), text: '#fff' };
                html += `<span class="evt-box" style="background:${color.bg};color:${color.text}">${evt.value}</span>`;
            });
            html += '</div>';
            cell.innerHTML = html;
        }
        function clearMonitorUI() {
            setText('header-match-id', 'MENUNGGU PERTANDINGAN');
            setText('peserta-nama-biru', 'Nama Atlet');
            setText('peserta-kontingen-biru', 'Asal Kontingen');
            setText('peserta-nama-merah', 'Nama Atlet');
            setText('peserta-kontingen-merah', 'Asal Kontingen');
            setText('peserta-partai', '-');
            
            const juriPositions = ['juri_1', 'juri_2', 'juri_3'];
            juriPositions.forEach(pos => {
                for (let r = 1; r <= 3; r++) {
                    let cellBlue = document.getElementById('val-blue-' + pos + '-r' + r);
                    if (cellBlue) cellBlue.innerHTML = '';
                    let cellRed = document.getElementById('val-red-' + pos + '-r' + r);
                    if (cellRed) cellRed.innerHTML = '';
                }
                setText('val-blue-' + pos + '-total', '0');
                setText('val-red-' + pos + '-total', '0');
            });
            
            for (let r = 1; r <= 3; r++) {
                setText('val-blue-jatuhan-r' + r, '');
                setText('val-red-jatuhan-r' + r, '');
                setText('val-blue-binaan-r' + r, '');
                setText('val-red-binaan-r' + r, '');
                setText('val-blue-hukuman-r' + r, '');
                setText('val-red-hukuman-r' + r, '');
            }
            
            setText('val-blue-jatuhan-total', '0');
            setText('val-red-jatuhan-total', '0');
            setText('val-blue-hukuman-total', '0');
            setText('val-red-hukuman-total', '0');
            
            setText('grand-total-blue', '0');
            setText('grand-total-red', '0');

            setText('pemenang-value', '-');
            setText('timer-value', '00:00');
        }

        let isFetchingMonitor = false;
        function updateMonitor() {
            if (isFetchingMonitor) return;
            isFetchingMonitor = true;

            fetch('{{ route("ketua.monitor.data") }}?_t=' + new Date().getTime())
                .then(res => {
                    isFetchingMonitor = false;
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        console.warn('Monitor: ' + (data.message || 'No data'));
                        clearMonitorUI();
                        return;
                    }

                    // === MATCH COMPLETION REDIRECT & CLEAR UI ===
                    if (currentMatchStatus === null) {
                        // Initial page load
                        if (data.match.status === 'finished' || data.match.status === 'final') {
                            // Match is already finished. Just clear UI and don't render old data.
                            clearMonitorUI();
                            currentMatchId = data.match.id;
                            currentMatchStatus = data.match.status;
                            return;
                        }
                    } else if (currentMatchStatus === 'playing' && (data.match.status === 'finished' || data.match.status === 'final')) {
                        // Match just finished while watching!
                        if (!window.isRedirecting) {
                            window.isRedirecting = true;
                            showTimerNotification("Pertandingan Selesai! Mengalihkan ke halaman Akurasi Juri...");
                            setTimeout(() => {
                                window.location.href = '{{ route("ketua.akurasi") }}';
                            }, 3000);
                        }
                        // Continue rendering to show final score during the 3-second wait
                    } else if (data.match.status === 'finished' || data.match.status === 'final') {
                        // Subsequent polls after it finished (while waiting to redirect, or if redirect failed)
                        // Allow rendering to continue showing the final score, or clear it if they somehow bypassed it.
                        if (!window.isRedirecting) {
                            clearMonitorUI();
                            return;
                        }
                    }
                    
                    currentMatchId = data.match.id;
                    currentMatchStatus = data.match.status;

                    // === SUBSCRIBE ke kanal pertandingan ini (setelah ID diketahui) ===
                    if (typeof window.Echo !== 'undefined' && subscribedMatchId !== currentMatchId) {
                        if (subscribedMatchId !== null) {
                            window.Echo.leaveChannel('match.' + subscribedMatchId);
                        }
                        window.Echo.channel('match.' + currentMatchId)
                            .listen('MatchUpdated', (e) => {
                                updateMonitor();
                            })
                            .listen('TimerStateUpdated', (e) => {
                                let serverTime = Math.round(e.time_remaining || 0);
                                syncLocalTimer(serverTime, e.status);

                                let currentTimerStatus = e.status;
                                let newRound = e.round || 1;
                                
                                if (serverTime === 0 && previousTimeRemaining > 0) {
                                    if (newRound == 3) {
                                        showTimerNotification("Waktu pertandingan telah habis");
                                    } else {
                                        showTimerNotification("Waktu babak " + newRound + " telah habis");
                                    }
                                } else if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused') && serverTime > 0) {
                                    showTimerNotification("Waktu babak " + newRound + " di jeda");
                                }
                                previousTimerStatus = currentTimerStatus;
                                previousRound = newRound;
                                previousTimeRemaining = e.time_remaining;
                            });
                        subscribedMatchId = currentMatchId;
                    }

                    // === HEADER ===
                    setText('header-match-id', 'MATCH - ' + (data.match.partai || '00'));

                    // === PESERTA ===
                    setText('peserta-nama-biru', data.match.sudut_biru || '-');
                    setText('peserta-kontingen-biru', data.match.kontingen_biru || '-');
                    setText('peserta-nama-merah', data.match.sudut_merah || '-');
                    setText('peserta-kontingen-merah', data.match.kontingen_merah || '-');
                    setText('peserta-partai', data.match.partai || '-');

                    // === JURI SCORES PER ROUND (total angka) ===
                    const juriPositions = ['juri_1', 'juri_2', 'juri_3'];
                    juriPositions.forEach(pos => {
                        const juriData = data.juri_scores[pos];
                        if (!juriData) return;

                        for (let r = 1; r <= 3; r++) {
                            // Render event history boxes instead of plain total
                            renderEventBoxes('val-blue-' + pos + '-r' + r, data.event_history, pos, r, 'blue');
                            renderEventBoxes('val-red-' + pos + '-r' + r, data.event_history, pos, r, 'red');
                        }
                    });

                    // === SCORE TOTALS PER ROUND ===
                    for (let r = 1; r <= 3; r++) {
                        // Render kotak nilai tervalidasi untuk baris SCORE
                        renderAwardBoxes('val-blue-score-r' + r, data.award_history, r, 'blue');
                        renderAwardBoxes('val-red-score-r' + r, data.award_history, r, 'red');

                        const rt = data.round_totals[r];
                        
                        let totalJuriBlue = rt ? rt.blue : 0;
                        let totalJuriRed = rt ? rt.red : 0;

                        // Menampilkan Total Juri per ronde
                        setText('juri-total-blue-' + r, totalJuriBlue > 0 ? totalJuriBlue : '0');
                        setText('juri-total-red-' + r, totalJuriRed > 0 ? totalJuriRed : '0');

                        const pf = data.penalties_formatted[r];
                        let roundTotalBlue = totalJuriBlue;
                        let roundTotalRed = totalJuriRed;

                        if (pf) {
                            roundTotalBlue += pf.jatuhan_biru_points || 0;
                            roundTotalBlue -= pf.hukuman_biru_points || 0;

                            roundTotalRed += pf.jatuhan_merah_points || 0;
                            roundTotalRed -= pf.hukuman_merah_points || 0;
                        }

                        // Cegah nilai negatif
                        if (roundTotalBlue < 0) roundTotalBlue = 0;
                        if (roundTotalRed < 0) roundTotalRed = 0;

                        // Menampilkan Grand Total per ronde
                        setText('round-total-blue-' + r, roundTotalBlue);
                        setText('round-total-red-' + r, roundTotalRed);
                    }

                    // === PENALTIES (Per Ronde) ===
                    const penFormatted = data.penalties_formatted;
                    for (let r = 1; r <= 3; r++) {
                        const pf = penFormatted[r];
                        if (pf) {
                            setText('val-blue-jatuhan-r' + r, pf.jatuhan_biru);
                            setText('val-red-jatuhan-r' + r, pf.jatuhan_merah);
                            
                            setText('val-blue-hukuman-r' + r, pf.hukuman_biru);
                            setText('val-red-hukuman-r' + r, pf.hukuman_merah);
                            
                            setText('val-blue-binaan-r' + r, pf.binaan_biru);
                            setText('val-red-binaan-r' + r, pf.binaan_merah);
                        } else {
                            setText('val-blue-jatuhan-r' + r, '');
                            setText('val-red-jatuhan-r' + r, '');
                            setText('val-blue-hukuman-r' + r, '');
                            setText('val-red-hukuman-r' + r, '');
                            setText('val-blue-binaan-r' + r, '');
                            setText('val-red-binaan-r' + r, '');
                        }
                    }

                    // === GRAND TOTAL ===
                    setText('grand-total-blue', data.grand_total.blue);
                    setText('grand-total-red', data.grand_total.red);

                    // === PEMENANG ===
                    setText('pemenang-value', data.pemenang);

                    // === TIMER ===
                    const timeRemaining = data.timer.time_remaining ?? 0;
                    // Selalu reset dari server agar tidak drift
                    syncLocalTimer(Math.round(timeRemaining), data.timer.status);
                    localTimerStatus = data.timer.status;

                    let currentTimerStatus = data.timer.status;
                    let currentTimeRemaining = timeRemaining;
                    const currentRound = data.timer.round ?? 1;

                    if (currentTimeRemaining === 0 && previousTimeRemaining > 0) {
                        if (currentRound == 3) {
                            showTimerNotification("Waktu pertandingan telah habis");
                        } else {
                            showTimerNotification("Waktu babak " + currentRound + " telah habis");
                        }
                    } else if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused') && currentTimeRemaining > 0) {
                        showTimerNotification("Waktu babak " + currentRound + " di jeda");
                    }
                    previousTimerStatus = currentTimerStatus;
                    previousRound = currentRound;
                    previousTimeRemaining = currentTimeRemaining;
                })
                .catch(err => {
                    isFetchingMonitor = false;
                    console.error('Monitor fetch error:', err);
                });
        }

        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('system')
                .listen('SystemStateChanged', (e) => {
                    window.location.reload();
                });
            // NOTE: subscribe ke 'match.*' dilakukan di dalam updateMonitor()
            // setelah currentMatchId diketahui dari respons server.
        }
        
        updateMonitor();

        // === REALTIME POLLING FALLBACK ===
        // Memastikan data nilai juri, penalti, & timer selalu ter-update secara otomatis tanpa refresh page
        setInterval(() => {
            updateMonitor();
        }, 1000);
    </script>

</body>
</html>
