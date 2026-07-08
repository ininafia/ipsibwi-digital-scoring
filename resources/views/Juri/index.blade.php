<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juri IPSI</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 min-h-screen font-sans overflow-hidden">

    {{-- HEADER --}}
    @include('Juri.header')

    {{-- CONTENT --}}
    <main class="p-2">

        <div class="bg-gray-100 border border-gray-300 rounded-xl shadow-md p-3">

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

        let currentMatchId = '{{ $match->id ?? '' }}';

        function addScore(sudut, nilai) {
            if(!currentMatchId) {
                console.warn('addScore aborted: currentMatchId is empty');
                alert('Gagal: Tidak ada pertandingan aktif yang terpantau.');
                return;
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
                if(!data.success) {
                    console.error('addScore error:', data.message);
                    alert('Gagal menambah nilai: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan koneksi.');
            });
        }

        function deleteScore(sudut) {
            if(!currentMatchId) {
                alert('Gagal: Tidak ada pertandingan aktif.');
                return;
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
                if(!data.success) {
                    console.error('deleteScore error:', data.message);
                    alert('Gagal menghapus nilai: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan koneksi.');
            });
        }
        function updateJuriDisplay() {
            fetch('{{ route('operator.monitor-display.data') }}')
                .then(res => res.json())
                .then(res => {
                    if(res.success && res.match) {
                        // Update Match ID dynamically
                        currentMatchId = res.match.id || '';

                        // Update Data Peserta
                        document.getElementById('juri-nama-biru').innerText = res.match.sudut_biru && res.match.sudut_biru !== '-' ? res.match.sudut_biru : 'Nama Atlet';
                        document.getElementById('juri-sekolah-biru').innerText = res.match.kontingen_biru && res.match.kontingen_biru !== '-' ? res.match.kontingen_biru : 'Asal Kontingen';
                        document.getElementById('juri-nama-merah').innerText = res.match.sudut_merah && res.match.sudut_merah !== '-' ? res.match.sudut_merah : 'Nama Atlet';
                        document.getElementById('juri-sekolah-merah').innerText = res.match.kontingen_merah && res.match.kontingen_merah !== '-' ? res.match.kontingen_merah : 'Asal Kontingen';
                        document.getElementById('juri-partai').innerText = res.match.partai || '-';
                        
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
                                    const scores = res.data;
                                    
                                    const renderScores = (sudut, arr, roundId) => {
                                        const box = document.getElementById(`score-${sudut}-${roundId}`);
                                        if(!box) return;
                                        box.innerHTML = ''; // clear
                                        arr.forEach((s, idx) => {
                                            let displayValue = s.nilai == 1 ? '1' : '2';
                                            if(idx > 0) displayValue = '+' + displayValue;
                                            const span = document.createElement('span');
                                            span.className = sudut === 'biru' ? 'text-blue-800' : 'text-red-700';
                                            if (s.status === 'pending') {
                                                span.className += ' animate-pulse opacity-60';
                                            } else if (s.is_sah === false) {
                                                span.className += ' line-through opacity-50';
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
                        // Clear match ID if no active match
                        currentMatchId = '';
                    }
                })
                .catch(console.error);
        }

        setInterval(updateJuriDisplay, 1000);
        updateJuriDisplay();
    </script>
</body>
</html>