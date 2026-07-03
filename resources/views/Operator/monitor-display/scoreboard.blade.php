<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
    function updateMonitorDisplay() {
        fetch('{{ route('operator.monitor-display.data') }}')
            .then(res => res.json())
            .then(res => {
                if(res.success && res.data && res.match) {
                    
                    // Update Header / Match Data
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
                    const time = res.match.time_remaining || 0;
                    const minutes = Math.floor(time / 60);
                    const seconds = time % 60;
                    document.getElementById('monitor-timer').innerText = `${String(minutes).padStart(2, '0')} : ${String(seconds).padStart(2, '0')}`;

                    // Note: If you want to update score box you can do it here:
                    // document.getElementById('skor_biru').innerText = res.data.skor_biru;
                    // document.getElementById('skor_merah').innerText = res.data.skor_merah;
                    
                    // Update Binaan
                    updateBinaan('biru', res.data.binaan_biru);
                    updateBinaan('merah', res.data.binaan_merah);

                    // Update Teguran
                    updateTeguran('biru', res.data.teguran_biru);
                    updateTeguran('merah', res.data.teguran_merah);

                    // Update Peringatan
                    updatePeringatan('biru', res.data.peringatan_biru);
                    updatePeringatan('merah', res.data.peringatan_merah);

                    // Update Jatuhan Count
                    let jBiru = document.getElementById('jatuhan-count-biru');
                    if (jBiru) jBiru.innerText = res.data.jatuhan_biru;
                    let jMerah = document.getElementById('jatuhan-count-merah');
                    if (jMerah) jMerah.innerText = res.data.jatuhan_merah;
                }
            })
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

    setInterval(updateMonitorDisplay, 1000);
    updateMonitorDisplay();
</script>

</body>
</html>
