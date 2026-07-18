<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer Dashboard</title>

    @vite('resources/css/app.css')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body{
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-200 min-h-screen flex flex-col">

    {{-- HEADER --}}
    @include('Timer.header')

    <div class="p-6 flex-grow flex flex-col">

        <div class="bg-gray-100 border border-gray-300 shadow-md p-6 rounded-2xl flex-grow flex flex-col">

            {{-- TOPBAR --}}
            @include('Timer.topbar')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 items-stretch flex-grow">

                {{-- ROUND CARD --}}
                @include('Timer.round-card')

                {{-- TIMER CARD --}}
                @include('Timer.timer-card')

            </div>

        </div>

    </div>

    <!-- Axios for HTTP Requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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

        document.addEventListener('DOMContentLoaded', () => {
            let currentRound = 1;
            let timeRemaining = 120; // 2 minutes in seconds
            let timerInterval = null;
            let status = 'stopped';

            const timerDisplay = document.getElementById('timer-display');
            const roundBtns = document.querySelectorAll('.round-btn');
            const btnRoundReset = document.getElementById('btn-round-reset');
            
            const btnTimerToggle = document.getElementById('btn-timer-toggle');
            const btnTimerReset = document.getElementById('btn-timer-reset');

            function updateDisplay() {
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerDisplay.innerText = `${String(minutes).padStart(2, '0')} : ${String(seconds).padStart(2, '0')}`;
                
                const roundDisplay = document.getElementById('round-display');
                if (roundDisplay) {
                    roundDisplay.innerText = currentRound;
                }

                // Update Round buttons
                roundBtns.forEach(btn => {
                    const roundNum = parseInt(btn.innerText);
                    if (roundNum === currentRound) {
                        btn.classList.remove('bg-cyan-100', 'hover:bg-cyan-200', 'text-black');
                        btn.classList.add('bg-sky-300', 'hover:bg-sky-400', 'text-black');
                    } else {
                        btn.classList.remove('bg-sky-300', 'hover:bg-sky-400', 'text-black');
                        btn.classList.add('bg-cyan-100', 'hover:bg-cyan-200', 'text-black');
                    }
                });

                // Update Toggle Button UI
                if (status === 'playing') {
                    btnTimerToggle.innerHTML = '⏸ Pause';
                    btnTimerToggle.className = 'transition font-bold text-lg px-6 py-3 rounded-xl min-w-[120px] bg-yellow-400 hover:bg-yellow-500 text-black';
                } else {
                    btnTimerToggle.innerHTML = '▶ Start';
                    btnTimerToggle.className = 'transition font-bold text-lg px-6 py-3 rounded-xl min-w-[120px] bg-green-500 hover:bg-green-600 text-white';
                }
            }

            function syncState() {
                axios.post('/timer/sync', {
                    round: currentRound,
                    time_remaining: timeRemaining,
                    status: status,
                    _token: '{{ csrf_token() }}'
                }).catch(err => console.error(err));
            }

            function fetchState() {
                axios.get('/timer/state?_t=' + new Date().getTime()).then(res => {
                    const data = res.data;
                    currentRound = data.round || 1;
                    timeRemaining = data.time_remaining !== undefined ? data.time_remaining : 120;
                    status = data.status || 'stopped';
                    updateDisplay();

                    if (status === 'playing' && !timerInterval) {
                        startInterval();
                    } else if (status !== 'playing' && timerInterval) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                    }
                }).catch(err => console.error(err));
            }

            function startInterval() {
                if (timerInterval) return;
                timerInterval = setInterval(() => {
                    if (timeRemaining > 0) {
                        timeRemaining--;
                        updateDisplay();
                        syncState(); 
                    } else {
                        if (status === 'playing') {
                            if (currentRound < 3) {
                                showTimerNotification("Waktu Babak " + currentRound + " telah habis!");
                                currentRound++;
                                timeRemaining = 120;
                            } else {
                                showTimerNotification("Waktu Pertandingan telah selesai!");
                                timeRemaining = 0;
                            }
                        }
                        status = 'stopped';
                        clearInterval(timerInterval);
                        timerInterval = null;
                        updateDisplay();
                        syncState();
                    }
                }, 1000);
            }

            // Bind Round Buttons
            roundBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    currentRound = parseInt(btn.innerText);
                    updateDisplay();
                    syncState();
                });
            });

            btnRoundReset.addEventListener('click', () => {
                currentRound = 1;
                updateDisplay();
                syncState();
            });

            // Bind Timer Buttons
            btnTimerToggle.addEventListener('click', () => {
                if (status === 'playing') {
                    // Pause it
                    status = 'paused';
                    if (timerInterval) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                    }
                } else {
                    // Start it
                    status = 'playing';
                    startInterval();
                }
                updateDisplay();
                syncState();
            });

            btnTimerReset.addEventListener('click', () => {
                status = 'stopped';
                timeRemaining = 120;
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                updateDisplay();
                syncState();
            });

            // Initial fetch
            fetchState();
        });
    </script>
</body>
</html>