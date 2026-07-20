<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-100 flex flex-col min-h-screen m-0">

<!-- Navbar Atas -->
<div class="bg-white h-20 flex items-center justify-between px-6 shadow-sm shrink-0 w-full">
    <img src="{{ asset('images/logos/LOGO IPSI.png') }}" alt="Logo IPSI" class="w-14 h-14 object-contain">
    <a href="javascript:history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded-md text-sm font-medium flex items-center gap-2 transition duration-200">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali
    </a>
</div>

<!-- Konten Utama (Scoreboard) -->
<div class="flex-1 flex items-center justify-center py-2 px-2 w-full">
    <div class="w-full max-w-[1300px] bg-white border border-gray-300 shadow-md p-2 lg:p-4">
        
        @include('Operator.monitor-display.header')

        <!-- Kontainer Grid Utama dengan border tebal seperti gambar -->
        <div class="border-[2px] border-black flex h-[300px] lg:h-[350px]">

            <!-- Kiri: Pelanggaran Sudut Biru -->
            <div class="w-[12%] border-r-[2px] border-black">
                @include('Operator.monitor-display.foul-points', ['sudut' => 'biru'])
            </div>

            <!-- Tengah: Kotak Skor Utama & Nilai Juri -->
            <div class="w-[76%] flex flex-col">
                @include('Operator.monitor-display.score-box')
            </div>

            <!-- Kanan: Pelanggaran Sudut Merah -->
            <div class="w-[12%] border-l-[2px] border-black">
                @include('Operator.monitor-display.foul-points', ['sudut' => 'merah'])
            </div>

        </div>
    </div>
</div>

<script>
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

    let localTimeRemaining = 0;
    let localTimerStatus = 'stopped';
    let localTimerInterval = null;

    // Selalu reset interval dari waktu server agar tidak drift
    function syncLocalTimer(serverTime, timerStatus) {
        localTimeRemaining = serverTime;
        localTimerStatus = timerStatus;

        if (localTimerInterval) {
            clearInterval(localTimerInterval);
            localTimerInterval = null;
        }

        if (timerStatus === 'playing' && localTimeRemaining > 0) {
            localTimerInterval = setInterval(() => {
                if (localTimeRemaining > 0) {
                    localTimeRemaining--;
                    const minutes = Math.floor(localTimeRemaining / 60);
                    const seconds = localTimeRemaining % 60;
                    document.getElementById('monitor-timer').innerText = `${String(minutes).padStart(2, '0')} : ${String(seconds).padStart(2, '0')}`;
                } else {
                    clearInterval(localTimerInterval);
                    localTimerInterval = null;
                }
            }, 1000);
        }

        const minutes = Math.floor(localTimeRemaining / 60);
        const seconds = localTimeRemaining % 60;
        document.getElementById('monitor-timer').innerText = `${String(minutes).padStart(2, '0')} : ${String(seconds).padStart(2, '0')}`;
    }

    function updateMonitorDisplay() {
        fetch('{{ route('operator.monitor-display.data') }}?_t=' + new Date().getTime())
            .then(res => res.json())
            .then(res => {
                if(res.success && res.data && res.match) {
                    
                    // Update Header / Match Data
                    if ('match.' + res.match.id !== currentEchoChannel) {
                        subscribeToMatch(res.match.id);
                    }
                    document.getElementById('monitor-nama-biru').innerText = res.match.sudut_biru && res.match.sudut_biru !== '-' ? res.match.sudut_biru : 'Nama Atlet';
                    document.getElementById('monitor-sekolah-biru').innerText = res.match.kontingen_biru && res.match.kontingen_biru !== '-' ? res.match.kontingen_biru : 'Asal Kontingen';
                    document.getElementById('monitor-nama-merah').innerText = res.match.sudut_merah && res.match.sudut_merah !== '-' ? res.match.sudut_merah : 'Nama Atlet';
                    document.getElementById('monitor-sekolah-merah').innerText = res.match.kontingen_merah && res.match.kontingen_merah !== '-' ? res.match.kontingen_merah : 'Asal Kontingen';
                    
                    // Update Round Boxes
                    const activeRound = res.match.round || 1;
                    for (let i = 1; i <= 3; i++) {
                        const box = document.getElementById('box-round-' + i);
                        if (box) {
                            if (i == activeRound) {
                                box.className = 'flex-[1] border-b-[2px] border-black flex items-center justify-center text-xl lg:text-2xl font-bold bg-green-500 text-white';
                            } else {
                                box.className = 'flex-[1] border-b-[2px] border-black flex items-center justify-center text-xl lg:text-2xl font-bold bg-white text-black';
                            }
                        }
                    }
                    
                    // Selalu reset dari server agar tidak drift
                    syncLocalTimer(Math.round(res.match.time_remaining || 0), res.match.timer_status);
                    // Update Score Box
                    let elBiru = document.getElementById('skor_biru');
                    if (elBiru) elBiru.innerText = res.data.skor_biru || 0;
                    
                    let elMerah = document.getElementById('skor_merah');
                    if (elMerah) elMerah.innerText = res.data.skor_merah || 0;
                    
                    // Update Binaan
                    updateBinaan('biru', res.data.binaan_biru);
                    updateBinaan('merah', res.data.binaan_merah);

                    // Update Teguran
                    updateTeguran('biru', res.data.teguran_biru);
                    updateTeguran('merah', res.data.teguran_merah);

                    // Update Peringatan
                    updatePeringatan('biru', res.data.peringatan_biru);
                    updatePeringatan('merah', res.data.peringatan_merah);
                    
                    // Update Active Votes (Juri Inputs)
                    if (res.active_votes) {
                        const corners = ['blue', 'red'];
                        const techniques = ['punch', 'kick'];
                        const juris = ['J1', 'J2', 'J3'];
                        
                        corners.forEach(corner => {
                            const bgColor = corner === 'blue' ? 'bg-blue-600 text-white' : 'bg-red-600 text-white';
                            techniques.forEach(technique => {
                                juris.forEach(juri => {
                                    const spanId = `vote-${corner}-${technique}-${juri}`;
                                    const el = document.getElementById(spanId);
                                    if (el) {
                                        if (res.active_votes[corner] && res.active_votes[corner][technique] && res.active_votes[corner][technique].includes(juri)) {
                                            el.className = `transition-colors duration-150 px-2 py-0.5 rounded font-bold shadow-sm ${bgColor}`;
                                        } else {
                                            el.className = 'transition-colors duration-150 px-2 py-0.5 rounded font-bold bg-white text-gray-700';
                                        }
                                    }
                                });
                            });
                        });
                    }

                    // Update Jatuhan Count
                    let jBiru = document.getElementById('jatuhan-count-biru');
                    if (jBiru) jBiru.innerText = res.data.jatuhan_biru;
                    let jMerah = document.getElementById('jatuhan-count-merah');
                    if (jMerah) jMerah.innerText = res.data.jatuhan_merah;

                    let currentTimerStatus = res.match.timer_status;
                    if (previousTimerStatus === 'playing' && (currentTimerStatus === 'stopped' || currentTimerStatus === 'paused')) {
                        showTimerNotification("Waktu Babak Berhenti!");
                    }
                    previousTimerStatus = currentTimerStatus;

                    let currentTimeRemaining = res.match.time_remaining;
                    
                    if (previousRound !== null && activeRound > previousRound) {
                        showTimerNotification("Waktu Babak " + previousRound + " telah habis!");
                    } else if (previousRound !== null && activeRound === 3 && previousTimeRemaining > 0 && currentTimeRemaining === 0) {
                        showTimerNotification("Waktu Pertandingan telah selesai!");
                    }
                    
                    previousRound = activeRound;
                    previousTimeRemaining = currentTimeRemaining;

                } else {
                    // No active match, clear UI
                    document.getElementById('monitor-nama-biru').innerText = 'Nama Atlet';
                    document.getElementById('monitor-sekolah-biru').innerText = 'Asal Kontingen';
                    document.getElementById('monitor-nama-merah').innerText = 'Nama Atlet';
                    document.getElementById('monitor-sekolah-merah').innerText = 'Asal Kontingen';
                    document.getElementById('monitor-timer').innerText = '00 : 00';
                    
                    let elBiru = document.getElementById('skor_biru');
                    if (elBiru) elBiru.innerText = 0;
                    let elMerah = document.getElementById('skor_merah');
                    if (elMerah) elMerah.innerText = 0;
                    
                    updateBinaan('biru', 0);
                    updateBinaan('merah', 0);
                    updateTeguran('biru', 0);
                    updateTeguran('merah', 0);
                    updatePeringatan('biru', 0);
                    updatePeringatan('merah', 0);
                    
                    let jBiru = document.getElementById('jatuhan-count-biru');
                    if (jBiru) jBiru.innerText = 0;
                    let jMerah = document.getElementById('jatuhan-count-merah');
                    if (jMerah) jMerah.innerText = 0;

                    for (let i = 1; i <= 3; i++) {
                        const box = document.getElementById('box-round-' + i);
                        if (box) box.className = 'flex-[1] border-b-[2px] border-black flex items-center justify-center text-xl lg:text-2xl font-bold bg-white text-black';
                    }
                }            })
            .catch(console.error);
    }

    function updateBinaan(sudut, count) {
        let b1 = document.getElementById('binaan-1-' + sudut);
        let b2 = document.getElementById('binaan-2-' + sudut);
        
        if (b1) {
            b1.className = count >= 1 
                ? 'w-10 h-10 lg:w-14 lg:h-12 bg-yellow-400 flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300' 
                : 'w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300';
        }
        
        if (b2) {
            b2.className = count >= 2 
                ? 'w-10 h-10 lg:w-14 lg:h-12 bg-yellow-400 flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300' 
                : 'w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300';
        }
    }

    function updateTeguran(sudut, count) {
        let t1 = document.getElementById('teguran-1-' + sudut);
        let t2 = document.getElementById('teguran-2-' + sudut);
        
        if (t1) {
            t1.className = count >= 1 
                ? 'w-10 h-10 lg:w-14 lg:h-12 bg-yellow-400 flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300' 
                : 'w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300';
        }
        
        if (t2) {
            t2.className = count >= 2 
                ? 'w-10 h-10 lg:w-14 lg:h-12 bg-yellow-400 flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300' 
                : 'w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300';
        }
    }

    function updatePeringatan(sudut, count) {
        let p1 = document.getElementById('peringatan-1-' + sudut);
        let p2 = document.getElementById('peringatan-2-' + sudut);
        
        if (p1) {
            p1.className = count >= 1 
                ? 'w-10 h-10 lg:w-14 lg:h-12 bg-yellow-400 flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300' 
                : 'w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300';
        }
        
        if (p2) {
            p2.className = count >= 2 
                ? 'w-10 h-10 lg:w-14 lg:h-12 bg-yellow-400 flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300' 
                : 'w-10 h-10 lg:w-14 lg:h-12 bg-[#d6d6d6] flex items-center justify-center rounded-md overflow-hidden transition-colors duration-300';
        }
    }

    let currentEchoChannel = null;

    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('system')
            .listen('SystemStateChanged', (e) => {
                window.location.reload();
            });
            

    }

    function subscribeToMatch(matchId) {
        if (typeof window.Echo === 'undefined' || !matchId) return;
        
        if (currentEchoChannel) {
            window.Echo.leave(currentEchoChannel);
        }
        
        currentEchoChannel = 'match.' + matchId;
        window.Echo.channel(currentEchoChannel)
            .listen('MatchUpdated', (e) => {
                updateMonitorDisplay();
            });
    }

    updateMonitorDisplay();
</script>

</body>
</html>
