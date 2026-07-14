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
    <main class="p-4">

        {{-- PESERTA --}}
        @include('Dewan.penilaian-atlet.peserta')

        {{-- SCORE --}}
        @include('Dewan.penilaian-atlet.score-table')

        {{-- PANEL DEWAN --}}
        @include('Dewan.penilaian-atlet.panel-dewan')

    </main>

    <script>
        let currentMatchId = '{{ $pertandingan->id ?? 0 }}';
        let isActionPending = false;
        let latestDewanFetchId = 0;

        function updateDewanUI() {
            if (isActionPending) return;
            let currentFetchId = ++latestDewanFetchId;

            fetch('/dewan/penilaian-atlet/data')
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
                        }

                        for (let i = 1; i <= 3; i++) {
                            let cellBiru = document.getElementById('dewan-jatuhan-biru-' + i);
                            let cellMerah = document.getElementById('dewan-jatuhan-merah-' + i);
                            let cellBinaanBiru = document.getElementById('dewan-binaan-biru-' + i);
                            let cellBinaanMerah = document.getElementById('dewan-binaan-merah-' + i);
                            
                            if (cellBiru) {
                                cellBiru.innerText = '';
                            }
                            if (cellMerah) {
                                cellMerah.innerText = '';
                            }

                            let currentRound = matchData.round || 1;

                            let roundIndicator = document.getElementById('dewan-round-indicator-' + i);
                            if (roundIndicator) {
                                if (i == currentRound) {
                                    roundIndicator.className = "w-36 bg-[#31b057] text-white font-bold flex items-center justify-center shadow transition-colors duration-300";
                                } else {
                                    roundIndicator.className = "w-36 bg-[#c5c6cc] text-white font-bold flex items-center justify-center shadow transition-colors duration-300";
                                }
                            }

                            // Untuk Binaan, Hukuman, dan Jatuhan, kita tampilkan teks akumulatifnya di ronde yang sedang aktif
                            if (i == currentRound) {
                                if (cellBinaanBiru) cellBinaanBiru.innerText = skorData.binaan_biru > 0 ? skorData.binaan_biru : '';
                                if (cellBinaanMerah) cellBinaanMerah.innerText = skorData.binaan_merah > 0 ? skorData.binaan_merah : '';
                                
                                let cellHukumanBiru = document.getElementById('dewan-hukuman-biru-' + i);
                                let cellHukumanMerah = document.getElementById('dewan-hukuman-merah-' + i);
                                
                                if (cellHukumanBiru) cellHukumanBiru.innerText = displayData.hukuman_biru_text;
                                if (cellHukumanMerah) cellHukumanMerah.innerText = displayData.hukuman_merah_text;

                                if (cellBiru) cellBiru.innerText = displayData.jatuhan_biru_text;
                                if (cellMerah) cellMerah.innerText = displayData.jatuhan_merah_text;
                            } else {
                                if (cellBinaanBiru) cellBinaanBiru.innerText = '';
                                if (cellBinaanMerah) cellBinaanMerah.innerText = '';
                                
                                let cellHukumanBiru = document.getElementById('dewan-hukuman-biru-' + i);
                                let cellHukumanMerah = document.getElementById('dewan-hukuman-merah-' + i);
                                if (cellHukumanBiru) cellHukumanBiru.innerText = '';
                                if (cellHukumanMerah) cellHukumanMerah.innerText = '';
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
                    }
                })
                .catch(console.error);
        }

        setInterval(updateDewanUI, 1000);
        updateDewanUI();
    </script>
</body>
</html>
