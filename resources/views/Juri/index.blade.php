<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juri IPSI</title>

    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
</head>

<body class="bg-gray-200 h-screen flex flex-col font-sans overflow-hidden">

    {{-- HEADER --}}
    @include('Juri.header')

    {{-- CONTENT --}}
    <main class="p-2 flex-1 flex flex-col overflow-x-auto">

        <div class="bg-gray-100 border border-gray-300 rounded-xl shadow-md p-3 min-w-[768px] flex-1 flex flex-col justify-between">

            {{-- PESERTA --}}
            @include('Juri.peserta')

            {{-- SCORE --}}
            @include('Juri.score-table')

            {{-- PANEL JURI --}}
            @include('Juri.panel-juri')

        </div>

    </main>

    <script>
        let currentRound = 1;
        let currentJuriPosition = '{{ $posisiTarget }}';
        let currentMatchId = '{{ $match ? $match->id : "" }}';
        let subscribedMatchId = null;

        function formatTimer(totalSeconds) {
            if (!totalSeconds || totalSeconds < 0) return '00:00';
            const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
            const s = (totalSeconds % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }

        function showToast(message) {
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

        let localTimerStatus = 'stopped';
        let localTimerInterval = null;
        let localTimeRemaining = 0;

        // Selalu reset interval dari waktu server agar tidak drift
        function syncLocalTimer(serverTime, timerStatus) {
            localTimeRemaining = serverTime;
            localTimerStatus = timerStatus;

            // Reset interval lama
            if (localTimerInterval) {
                clearInterval(localTimerInterval);
                localTimerInterval = null;
            }

            // Jalankan interval baru jika sedang playing
            if (timerStatus === 'playing' && localTimeRemaining > 0) {
                localTimerInterval = setInterval(() => {
                    if (localTimeRemaining > 0) {
                        localTimeRemaining--;
                        let timerVal = document.getElementById('timer-value');
                        if (timerVal) timerVal.innerText = formatTimer(localTimeRemaining);
                    } else {
                        clearInterval(localTimerInterval);
                        localTimerInterval = null;
                    }
                }, 1000);
            }

            // Update display sekarang juga
            let timerVal = document.getElementById('timer-value');
            if (timerVal) timerVal.innerText = formatTimer(localTimeRemaining);
        }

        let isSubmittingScore = false;
        function addScore(sudut, nilai) {
            if(!currentMatchId) {
                console.warn('addScore aborted: currentMatchId is empty');
                showToast('Gagal: Tidak ada pertandingan aktif yang terpantau.');
                return;
            }

            if (isSubmittingScore) return;
            isSubmittingScore = true;

            fetch('{{ route('juri.input-score') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_pertandingan: currentMatchId,
                    id_babak: currentRound,
                    sudut: sudut,
                    id_kategori_nilai: nilai,
                    nilai: nilai
                })
            })
            .then(res => res.json())
            .then(data => {
                isSubmittingScore = false;
                if(!data.success) {
                    console.error('addScore error:', data.message);
                    showToast('Gagal menambah nilai: ' + data.message);
                } else {
                    updateJuriDisplay();
                }
            })
            .catch(err => {
                isSubmittingScore = false;
                console.error(err);
                showToast('Terjadi kesalahan koneksi.');
            });
        }

        function deleteScore(sudut) {
            if(!currentMatchId) {
                showToast('Gagal: Tidak ada pertandingan aktif.');
                return;
            }

            if (isSubmittingScore) return;
            isSubmittingScore = true;

            fetch('{{ route('juri.delete-score') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_pertandingan: currentMatchId,
                    id_babak: currentRound,
                    sudut: sudut
                })
            })
            .then(res => res.json())
            .then(data => {
                isSubmittingScore = false;
                if(!data.success) {
                    console.error('deleteScore error:', data.message);
                    showToast('Gagal menghapus nilai: ' + data.message);
                } else {
                    updateJuriDisplay();
                }
            })
            .catch(err => {
                isSubmittingScore = false;
                console.error(err);
                showToast('Terjadi kesalahan koneksi.');
            });
        }
        function updateJuriDisplay() {
            fetch('{{ route('operator.monitor-display.data') }}?_t=' + new Date().getTime())
                .then(res => res.json())
                .then(res => {
                    if(res.success && res.match) {
                        // Update Match ID dynamically
                        currentMatchId = res.match.id || '';

                        // Subscribe ke kanal match yang benar (setelah ID diketahui)
                        if (typeof window.Echo !== 'undefined' && currentMatchId && subscribedMatchId !== currentMatchId) {
                            if (subscribedMatchId) window.Echo.leaveChannel('match.' + subscribedMatchId);
                            window.Echo.channel('match.' + currentMatchId)
                                .listen('MatchUpdated', (e) => { updateJuriDisplay(); });
                            subscribedMatchId = currentMatchId;
                        }

                        // Update Data Peserta
                        document.getElementById('juri-nama-biru').innerText = res.match.sudut_biru && res.match.sudut_biru !== '-' ? res.match.sudut_biru : 'Nama Atlet';
                        document.getElementById('juri-sekolah-biru').innerText = res.match.kontingen_biru && res.match.kontingen_biru !== '-' ? res.match.kontingen_biru : 'Asal Kontingen';
                        document.getElementById('juri-nama-merah').innerText = res.match.sudut_merah && res.match.sudut_merah !== '-' ? res.match.sudut_merah : 'Nama Atlet';
                        document.getElementById('juri-sekolah-merah').innerText = res.match.kontingen_merah && res.match.kontingen_merah !== '-' ? res.match.kontingen_merah : 'Asal Kontingen';
                        document.getElementById('juri-partai').innerText = res.match.partai || '-';
                        
                        let timerVal = document.getElementById('timer-value');
                        if(timerVal) {
                            let serverTime = Math.round(res.match.time_remaining || 0);
                            // Selalu reset dari server agar tidak drift
                            syncLocalTimer(serverTime, res.match.timer_status);
                        }

                        let currentTimerStatus = res.match.timer_status;
                        if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused')) {
                            showToast("Waktu Babak Berhenti!");
                        }
                        previousTimerStatus = currentTimerStatus;

                        let currentTimeRemaining = res.match.time_remaining;
                        currentRound = res.match.round || 1;

                        if (previousRound !== null && currentRound > previousRound) {
                            showToast("Waktu Babak " + previousRound + " telah habis!");
                        } else if (previousRound !== null && currentRound === 3 && previousTimeRemaining > 0 && currentTimeRemaining === 0) {
                            showToast("Waktu Pertandingan telah selesai!");
                        }
                        previousRound = currentRound;
                        previousTimeRemaining = currentTimeRemaining;

                        // Update Round
                        currentRound = res.match.round || 1;
                        for (let i = 1; i <= 3; i++) {
                            const box = document.getElementById('juri-round-' + i);
                            if (box) {
                                if (i == currentRound) {
                                    box.className = 'h-10 bg-green-500 flex items-center justify-center text-lg font-bold text-white rounded';
                                } else {
                                    box.className = 'h-10 bg-gray-400 flex items-center justify-center text-lg font-bold text-white rounded';
                                }
                            }
                        }

                        // Fetch history using the updated match ID
                        fetch('{{ route('juri.history') }}?id_pertandingan=' + currentMatchId + '&id_babak=' + currentRound)
                            .then(res => res.json())
                            .then(res => {
                                if(res.success && res.data) {
                                    const scores = res.data.history;
                                    const juri = res.data.juri;

                                    // Update Nama Juri & Posisi Juri di panel
                                    document.getElementById('juri-nama-petugas').innerText = juri.nama;
                                    document.getElementById('juri-nama-posisi').innerText = juri.posisi;
                                    
                                    const renderScores = (sudut, arr, roundId) => {
                                        const box = document.getElementById(`score-${sudut}-${roundId}`);
                                        if(!box) return;
                                        box.innerHTML = ''; // clear
                                        
                                        arr.forEach((s, idx) => {
                                            console.log('Processing score:', s);
                                            if (s.status !== 'pending' && s.is_sah !== true) {
                                                console.log('Skipping score due to status/sah condition');
                                                return;
                                            }

                                            let displayValue = s.nilai == 1 ? '1' : '2';
                                            if(idx > 0) displayValue = '+' + displayValue;
                                            const span = document.createElement('span');
                                            
                                            if (s.status === 'pending') {
                                                span.className = 'text-gray-600 font-bold opacity-80 animate-pulse'; // Pending indicator
                                            } else {
                                                span.className = sudut === 'biru' ? 'text-blue-800' : 'text-red-700';
                                            }
                                            
                                            span.innerText = displayValue;
                                            box.appendChild(span);
                                        });
                                    };

                                    for(let r = 1; r <= 3; r++) {
                                        const roundScores = scores.filter(s => s.id_babak == r);
                                        const blueScores = roundScores.filter(s => s.sudut === 'biru');
                                        const redScores = roundScores.filter(s => s.sudut === 'merah');
                                        renderScores('biru', blueScores, r);
                                        renderScores('merah', redScores, r);
                                    }
                                }
                            })
                            .catch(console.error);
                    } else {
                        // Clear match ID and UI if no active match
                        currentMatchId = '';
                        document.getElementById('juri-nama-biru').innerText = 'Nama Atlet';
                        document.getElementById('juri-sekolah-biru').innerText = 'Asal Kontingen';
                        document.getElementById('juri-nama-merah').innerText = 'Nama Atlet';
                        document.getElementById('juri-sekolah-merah').innerText = 'Asal Kontingen';
                        document.getElementById('juri-partai').innerText = '-';
                        
                        let timerVal = document.getElementById('timer-value');
                        if (timerVal) timerVal.innerText = '00:00';

                        for (let r = 1; r <= 3; r++) {
                            let boxB = document.getElementById(`score-biru-${r}`);
                            if (boxB) boxB.innerHTML = '';
                            let boxM = document.getElementById(`score-merah-${r}`);
                            if (boxM) boxM.innerHTML = '';
                        }
                    }
                })
                .catch(console.error);
        }

        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('system')
                .listen('SystemStateChanged', (e) => {
                    window.location.reload();
                });
            // NOTE: subscribe 'match.*' dilakukan di dalam updateJuriDisplay() setelah ID diketahui
        }

        updateJuriDisplay();
    </script>
</body>
</html>