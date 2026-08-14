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
    <main class="p-2 sm:p-3 flex-1 flex flex-col justify-center overflow-hidden">

        <div class="bg-gray-100 border border-gray-300 rounded-xl shadow-md p-3 sm:p-4 w-full flex flex-col gap-3 sm:gap-4 my-auto overflow-hidden">

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

        function showToast(message) {
            showTimerNotification(message);
        }

        let previousTimerStatus = null;
        let previousTimeRemaining = null;
        let previousRound = null;

        let localTimerStatus = 'stopped';
        let localTimerInterval = null;
        let localTimeRemaining = 0;

        let targetEndTime = null;

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
                targetEndTime = Date.now() + (localTimeRemaining * 1000);
                localTimerInterval = setInterval(() => {
                    let newTime = Math.ceil((targetEndTime - Date.now()) / 1000);
                    if (newTime < 0) newTime = 0;
                    if (newTime !== localTimeRemaining) {
                        localTimeRemaining = newTime;
                        let timerVal = document.getElementById('timer-value');
                        if (timerVal) timerVal.innerText = formatTimer(localTimeRemaining);
                        
                        if (localTimeRemaining <= 0) {
                            clearInterval(localTimerInterval);
                            localTimerInterval = null;
                        }
                    }
                }, 200);
            }

            // Trigger Rekam Layar Juri saat Timer Playing (hanya jika belum merekam, tidak sedang meminta izin, & tidak ditolak)
            if (timerStatus === 'playing') {
                if (!isRecording && !isRequestingPermission && !userDismissedPrompt) {
                    autoStartRecording();
                }
            } else if (timerStatus === 'stopped' || timerStatus === 'paused') {
                if (isRecording) {
                    stopAutoRecording();
                }
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

            // OPTIMISTIC UPDATE
            let box = document.getElementById(`score-${sudut}-${currentRound}`);
            if (box) {
                let displayValue = nilai == 1 ? '1' : '2';
                if (box.children.length > 0) displayValue = '+' + displayValue;
                const span = document.createElement('span');
                span.className = 'text-gray-600 font-bold opacity-80 animate-pulse';
                span.innerText = displayValue;
                box.appendChild(span);
            }

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
                    updateJuriScores(); // revert optimistic update
                } else {
                    updateJuriScores();
                }
            })
            .catch(err => {
                isSubmittingScore = false;
                console.error(err);
                showToast('Terjadi kesalahan koneksi.');
                updateJuriScores();
            });
        }

        function deleteScore(sudut) {
            if(!currentMatchId) {
                showToast('Gagal: Tidak ada pertandingan aktif.');
                return;
            }

            if (isSubmittingScore) return;
            isSubmittingScore = true;

            // OPTIMISTIC UPDATE
            let box = document.getElementById(`score-${sudut}-${currentRound}`);
            let hiddenElement = null;
            if (box && box.lastElementChild) {
                hiddenElement = box.lastElementChild;
                hiddenElement.classList.add('hidden');
            }

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
                    if (hiddenElement) hiddenElement.classList.remove('hidden'); // revert
                    updateJuriScores();
                } else {
                    updateJuriScores();
                }
            })
            .catch(err => {
                isSubmittingScore = false;
                console.error(err);
                showToast('Terjadi kesalahan koneksi.');
                if (hiddenElement) hiddenElement.classList.remove('hidden'); // revert
                updateJuriScores();
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
                                .listen('MatchUpdated', (e) => { updateJuriScores(); })
                                .listen('TimerStateUpdated', (e) => {
                                    let serverTime = Math.round(e.time_remaining || 0);
                                    syncLocalTimer(serverTime, e.status);
                                    let currentTimerStatus = e.status;
                                    currentRound = e.round || 1;

                                    if (serverTime === 0 && previousTimeRemaining > 0) {
                                        if (currentRound == 3) {
                                            showToast("Waktu pertandingan telah habis");
                                        } else {
                                            showToast("Waktu babak " + currentRound + " telah habis");
                                        }
                                    } else if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused') && serverTime > 0) {
                                        showToast("Waktu babak " + currentRound + " di jeda");
                                    }
                                    previousTimerStatus = currentTimerStatus;
                                    previousTimeRemaining = serverTime;

                                    // Update Round indicator
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
                                });
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
                        let currentTimeRemaining = res.match.time_remaining;
                        currentRound = res.match.round || 1;

                        if (currentTimeRemaining === 0 && previousTimeRemaining > 0) {
                            if (currentRound == 3) {
                                showToast("Waktu pertandingan telah habis");
                            } else {
                                showToast("Waktu babak " + currentRound + " telah habis");
                            }
                        } else if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused') && currentTimeRemaining > 0) {
                            showToast("Waktu babak " + currentRound + " di jeda");
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
                        updateJuriScores();
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

        function updateJuriScores() {
            if (!currentMatchId) return;
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
                                if (s.status !== 'pending' && s.is_sah !== true) {
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
        }

        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('system')
                .listen('SystemStateChanged', (e) => {
                    window.location.reload();
                });

            // Subscribe langsung ke match channel jika ID sudah diketahui dari PHP
            if (currentMatchId) {
                window.Echo.channel('match.' + currentMatchId)
                    .listen('MatchUpdated', (e) => { updateJuriScores(); })
                    .listen('TimerStateUpdated', (e) => {
                        let serverTime = Math.round(e.time_remaining || 0);
                        syncLocalTimer(serverTime, e.status);

                        let currentTimerStatus = e.status;
                        let currentTimeRem = Math.round(e.time_remaining || 0);
                        let newRound = e.round || 1;

                        if (currentTimeRem === 0 && previousTimeRemaining > 0) {
                            if (newRound == 3) {
                                showToast("Waktu pertandingan telah habis");
                            } else {
                                showToast("Waktu babak " + newRound + " telah habis");
                            }
                        } else if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused') && currentTimeRem > 0) {
                            showToast("Waktu babak " + newRound + " di jeda");
                        }

                        currentRound = newRound;
                        previousRound = newRound;
                        previousTimeRemaining = currentTimeRem;

                        // Update Round indicator
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
                    });
                subscribedMatchId = currentMatchId;
            }
            // NOTE: subscribe 'match.*' juga dilakukan di dalam updateJuriDisplay() setelah ID diketahui
        }

        updateJuriDisplay();

        // === TIMER POLLING FALLBACK ===
        setInterval(() => {
            if (!currentMatchId) return;
            fetch('/timer/state/poll?id_pertandingan=' + currentMatchId + '&_t=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    if (data.error) return;

                    let serverTime = Math.round(data.time_remaining || 0);
                    let serverStatus = data.status || 'stopped';
                    let serverRound = data.round || 1;

                    if (serverStatus !== localTimerStatus || 
                        Math.abs(serverTime - localTimeRemaining) > 1 ||
                        serverRound !== currentRound) {
                        
                        syncLocalTimer(serverTime, serverStatus);

                        if (serverTime === 0 && previousTimeRemaining > 0) {
                            if (serverRound == 3) {
                                showToast("Waktu pertandingan telah habis");
                            } else {
                                showToast("Waktu babak " + serverRound + " telah habis");
                            }
                        } else if (previousTimerStatus === 'playing' && (serverStatus === 'stopped' || serverStatus === 'paused') && serverTime > 0) {
                            showToast("Waktu babak " + serverRound + " di jeda");
                        }
                        
                        previousTimerStatus = serverStatus;
                        previousTimeRemaining = serverTime;
                        previousRound = serverRound;
                        currentRound = serverRound;

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
                    }
                })
                .catch(() => {});
        }, 2000);

        // === AUTOMATIC SCREEN RECORDING FEATURE (REKAM LAYAR OTOMATIS) ===
        let mediaRecorder = null;
        let recordedChunks = [];
        let recordStartTime = null;
        let isRecording = false;
        let isRequestingPermission = false;
        let userDismissedPrompt = false;
        let streamObject = null;

        function autoStartRecording() {
            if (isRecording || isRequestingPermission || userDismissedPrompt || !currentMatchId) return;

            // If stream is already active, reuse it without prompting browser again
            if (streamObject && streamObject.active && streamObject.getVideoTracks().length > 0 && streamObject.getVideoTracks()[0].readyState === 'live') {
                handleRecordingStream(streamObject);
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
                isRequestingPermission = false;
                userDismissedPrompt = true;
                let btn = document.getElementById('start-rec-btn');
                if (btn) {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                    btn.innerText = '⚠️ Rekam Layar butuh HTTPS di Server';
                    btn.disabled = true;
                    btn.className = 'fixed bottom-4 right-4 bg-amber-600 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg flex items-center gap-2 z-50 cursor-not-allowed opacity-90';
                }
                console.warn('Display Media API is not available (requires HTTPS or localhost).');
                return;
            }

            isRequestingPermission = true;

            navigator.mediaDevices.getDisplayMedia({
                video: { cursor: 'always' },
                audio: false
            })
            .then(stream => {
                isRequestingPermission = false;
                userDismissedPrompt = false;
                handleRecordingStream(stream);
            })
            .catch(err => {
                isRequestingPermission = false;
                userDismissedPrompt = true; // Stop asking repeatedly on cancel/dismiss!
                let btn = document.getElementById('start-rec-btn');
                if (btn) {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                }
                console.warn('Display Media cancelled or unallowed:', err);
            });
        }

        function startManualRecording() {
            userDismissedPrompt = false;
            let btn = document.getElementById('start-rec-btn');
            if (btn) btn.classList.add('hidden');
            autoStartRecording();
        }

        function handleRecordingStream(stream) {
            streamObject = stream;
            recordedChunks = [];
            recordStartTime = Date.now();

            let options = { mimeType: 'video/webm' };
            if (!MediaRecorder.isTypeSupported('video/webm')) {
                options = { mimeType: 'video/mp4' };
            }

            mediaRecorder = new MediaRecorder(stream, options);
            mediaRecorder.ondataavailable = e => {
                if (e.data && e.data.size > 0) {
                    recordedChunks.push(e.data);
                }
            };

            mediaRecorder.onstop = () => {
                if (recordedChunks.length > 0) {
                    const blob = new Blob(recordedChunks, { type: options.mimeType });
                    const duration = Math.round((Date.now() - recordStartTime) / 1000);
                    uploadRecordedVideo(blob, duration);
                }
                recordedChunks = [];
                isRecording = false;
                let badge = document.getElementById('rec-status-badge');
                if (badge) {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
            };

            if (stream.getVideoTracks().length > 0) {
                stream.getVideoTracks()[0].onended = () => {
                    stopAutoRecording(true);
                };
            }

            mediaRecorder.start(1000);
            isRecording = true;
            let badge = document.getElementById('rec-status-badge');
            if (badge) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            }
            let btn = document.getElementById('start-rec-btn');
            if (btn) btn.classList.add('hidden');
        }

        function stopAutoRecording(closeStream = true) {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
            }
            if (closeStream && streamObject) {
                streamObject.getTracks().forEach(track => track.stop());
                streamObject = null;
            }
        }

        function uploadRecordedVideo(blob, duration) {
            if (!currentMatchId) return;

            const formData = new FormData();
            formData.append('video', blob, 'screen_juri.webm');
            formData.append('id_pertandingan', currentMatchId);
            formData.append('posisi_juri', currentJuriPosition);
            formData.append('duration', duration);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("juri.upload-video") }}', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log('Automated screen recording uploaded successfully:', data.data);
                } else {
                    console.warn('Automated video upload failed:', data.message);
                }
            })
            .catch(err => console.error('Automated video upload error:', err));
        }

        setInterval(() => {
            if (isRecording && mediaRecorder && currentMatchId && localTimerStatus === 'playing') {
                // Request chunk video upload without killing the active screen capture track
                stopAutoRecording(false);
                setTimeout(() => {
                    if (streamObject && streamObject.active) {
                        handleRecordingStream(streamObject);
                    }
                }, 500);
            }
        }, 60000);
    </script>
</body>
</html>