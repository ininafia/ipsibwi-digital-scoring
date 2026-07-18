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
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    @include('Ketua.Monitor-Ketua.header')

    <!-- Main Content -->
    <main class="flex-1 p-6 flex flex-col">
        <div class="bg-white flex-1 shadow-md border border-gray-200 flex flex-col p-6">
            
            <!-- Peserta -->
            @include('Ketua.Monitor-Ketua.peserta')

            <!-- Grid Content -->
            <div class="grid grid-cols-5 gap-4 flex-1 items-start">
                
                <!-- Table Kiri -->
                <div class="col-span-4 overflow-hidden">
                    @include('Ketua.Monitor-Ketua.score-table')
                </div>

                <!-- Panel Kanan -->
                <div class="col-span-1">
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

        const awardColors = [
            'bg-[#ff3b8f] text-white', // pink
            'bg-[#ffcc00] text-black', // yellow
            'bg-[#2d2d2d] text-white', // black
            'bg-[#8b3dff] text-white', // purple
            'bg-[#10b981] text-white', // green
            'bg-[#f97316] text-white', // orange
            'bg-[#0ea5e9] text-white', // light blue
            'bg-[#eab308] text-white', // dark yellow
            'bg-[#ec4899] text-white', // alternate pink
            'bg-[#84cc16] text-white'  // lime
        ];

        function getColorForAward(awardId) {
            if (!awardId) return 'bg-gray-400 text-white';
            let hash = 0;
            for (let i = 0; i < awardId.length; i++) {
                hash = awardId.charCodeAt(i) + ((hash << 5) - hash);
            }
            const index = Math.abs(hash) % awardColors.length;
            return awardColors[index];
        }

        function renderEventBoxes(cellId, eventHistory, juriPos, round, athlete) {
            const cell = document.getElementById(cellId);
            if (!cell) return;

            // Check if event_history data exists
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
                    // Sah: colored box based on award_id
                    const colorClass = evt.award_id ? getColorForAward(evt.award_id) : (athlete === 'blue' ? 'evt-sah-blue' : 'evt-sah-red');
                    html += `<span class="evt-box ${colorClass}">${evt.value}</span>`;
                } else {
                    // Tidak sah: strikethrough style
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
                const colorClass = evt.award_id ? getColorForAward(evt.award_id) : (athlete === 'blue' ? 'evt-sah-blue' : 'evt-sah-red');
                html += `<span class="evt-box ${colorClass}">${evt.value}</span>`;
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

        function updateMonitor() {
            fetch('{{ route("ketua.monitor.data") }}?_t=' + new Date().getTime())
                .then(res => {
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
                    setText('timer-value', formatTimer(Math.round(timeRemaining)));

                    let currentTimerStatus = data.timer.status;
                    if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused')) {
                        showTimerNotification("Waktu Babak Berhenti!");
                    }
                    previousTimerStatus = currentTimerStatus;

                    let currentTimeRemaining = timeRemaining;
                    const currentRound = data.timer.round ?? 1;

                    if (previousRound !== null && currentRound > previousRound) {
                        showTimerNotification("Waktu Babak " + previousRound + " telah habis!");
                    } else if (previousRound !== null && currentRound === 3 && previousTimeRemaining > 0 && currentTimeRemaining === 0) {
                        showTimerNotification("Waktu Pertandingan telah selesai!");
                    }
                    previousRound = currentRound;
                    previousTimeRemaining = currentTimeRemaining;
                })
                .catch(err => {
                    console.error('Monitor fetch error:', err);
                });
        }

        // Poll setiap 1 detik
        setInterval(updateMonitor, 1000);
        updateMonitor();
    </script>

</body>
</html>
