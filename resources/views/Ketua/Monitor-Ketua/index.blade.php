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

        function formatTimer(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return String(m).padStart(2, '0') + ' : ' + String(s).padStart(2, '0');
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
                    // Sah: colored box with value
                    const colorClass = athlete === 'blue' ? 'evt-sah-blue' : 'evt-sah-red';
                    html += '<span class="evt-box ' + colorClass + '">' + evt.value + '</span>';
                } else {
                    // Tidak sah: strikethrough style
                    html += '<span class="evt-box evt-tidak-sah">' + evt.value + '</span>';
                }
            });
            html += '</div>';
            cell.innerHTML = html;
        }

        function updateMonitor() {
            fetch('{{ route("ketua.monitor.data") }}')
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        console.warn('Monitor: ' + (data.message || 'No data'));
                        return;
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
                        const rt = data.round_totals[r];
                        setText('val-blue-score-r' + r, rt ? rt.blue : '');
                        setText('val-red-score-r' + r, rt ? rt.red : '');
                        setText('round-total-blue-' + r, rt ? rt.blue : '');
                        setText('round-total-red-' + r, rt ? rt.red : '');
                    }

                    // === PENALTIES (same values for all rounds for now) ===
                    const pen = data.penalties;
                    for (let r = 1; r <= 3; r++) {
                        setText('val-blue-jatuhan-r' + r, pen.jatuhan_biru || '');
                        setText('val-red-jatuhan-r' + r, pen.jatuhan_merah || '');
                        // Hukuman = teguran + peringatan
                        const hukumanBiru = (pen.teguran_biru || 0) + (pen.peringatan_biru || 0);
                        const hukumanMerah = (pen.teguran_merah || 0) + (pen.peringatan_merah || 0);
                        setText('val-blue-hukuman-r' + r, hukumanBiru || '');
                        setText('val-red-hukuman-r' + r, hukumanMerah || '');
                        setText('val-blue-binaan-r' + r, pen.binaan_biru || '');
                        setText('val-red-binaan-r' + r, pen.binaan_merah || '');
                    }

                    // === GRAND TOTAL ===
                    setText('grand-total-blue', data.grand_total.blue);
                    setText('grand-total-red', data.grand_total.red);

                    // === PEMENANG ===
                    setText('pemenang-value', data.pemenang);

                    // === TIMER ===
                    const timeRemaining = data.timer.time_remaining ?? 0;
                    setText('timer-value', formatTimer(Math.round(timeRemaining)));

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
