app/Http/Usecases/JuriUsecase.php
Setelah $lastInput ditemukan dan dihapus, log memakai $eventToDelete->id, padahal variabel $eventToDelete tidak pernah dibuat. 
Seeder user juri masih salah mapping role. uri2 diberi access_type = 6 dan juri3 = 7. Padahal di SQL terbaru, semua juri default sudah access_type = 5. Kalau method ini dijalankan, akun juri2 dan juri3 bisa berubah menjadi role wasit/delegasi, lalu tidak bisa akses route juri.
Sistem membedakan “Username tidak ditemukan” dan “Password salah”. Ini memudahkan penyerang mengetahui username valid. Untuk production, sebaiknya satu pesan umum: “Username atau password salah.”
app/Http/Usecases/PertandinganUsecase.php pada method finalizeMatch()
Backend menerima jenis kemenangan angka, teknik, mutlak, wmp, disk, undur_diri, tetapi pemenang selalu dihitung dari skor. Ini salah untuk diskualifikasi, undur diri, menang teknik, atau mutlak. Dalam kasus tersebut pemenang bisa bukan yang unggul skor saat itu.
PertandinganUsecase.php
Sistem hanya menolak finalisasi jika timer masih playing. Jika timer paused atau stopped di ronde 1, pertandingan bisa difinalisasi. Untuk kemenangan angka, seharusnya cek ronde 3 selesai atau ada keputusan khusus.
PertandinganUsecase.php. Jika skor biru dan merah sama, winner_corner dan winner_name menjadi null, tetapi pertandingan tetap disimpan sebagai finished. Dalam pertandingan resmi, seri butuh mekanisme keputusan lanjutan, bukan selesai tanpa pemenang.
app/Http/Usecases/JuriUsecase.php
Window scoring sekarang sudah memisahkan athlete dan technique. Tetapi jika terjadi dua tendangan untuk sudut yang sama dalam rentang 3 detik, sistem masih bisa menganggapnya satu event. Ini rawan pada pertandingan cepat.
JuriUsecase.php dan PenilaianAtletUsecase.php .Sistem menyimpan event (score_awards, score_events), tetapi total skor tetap langsung ditambah/dikurangi di skor_pertandingan. Jika terjadi bug, delete, rollback parsial, atau koreksi, agregat bisa tidak sinkron dengan event. Idealnya total skor dihitung ulang dari event log.
JuriUsecase.php Secara audit, menghapus baris score event membuat jejak input hilang. Untuk pertandingan resmi, lebih baik status diubah menjadi deleted/cancelled dengan deleted_by, deleted_at, dan alasan, bukan physical delete.
Sudah ada riwayat_hukuman, tetapi belum ada alasan, catatan, atau referensi keputusan dewan/ketua. Untuk audit pertandingan, ini masih kurang.
app/Http/Usecases/PetugasUsecase.php nput hanya dicek exists:data_petugas,id. Tidak dicek apakah petugas yang dipilih sebagai juri memang tugas = Juri, yang dipilih sebagai dewan memang Dewan, dan seterusnya.
app/Http/Usecases/PetugasUsecase.php Tidak ada validasi duplikasi. Satu id_petugas bisa dipasang sebagai ketua, dewan, wasit, dan juri sekaligus dalam pertandingan yang sama.
PetugasUsecase.php method assignPetugas() Jika juri1 kosong tetapi juri2 diisi, sistem tetap menyimpan orang tersebut sebagai juri_1 karena $juriNumber hanya naik saat ada input. Ini bisa membuat assignment tidak sesuai form.
Tabel petugas_pertandingan tidak punya UNIQUE(id_pertandingan, posisi). Secara database, dua juri_1 untuk pertandingan yang sama masih mungkin terjadi.
app/Http/Usecases/PetugasUsecase.php → getByID() Query mengambil created_at dan updated_at, tetapi tabel data_petugas di SQL hanya punya id, nama, tugas, deleted_at. Fitur edit/detail petugas bisa error SQL.
app/Http/Usecases/WaitingListUsecase.php Login tidak memakai Laravel Auth, tetapi audit created_by, updated_by, deleted_by memakai Auth::id(). Nilainya berisiko null. Harus konsisten pakai session('user_id') atau pindah ke Laravel Auth penuh.
skor_pertandingan.id_pertandingan belum unique.Bisa ada lebih dari satu row skor untuk satu pertandingan. Banyak query memakai first(), sehingga hasil bisa tidak pasti.
Tidak ada unique constraint seperti UNIQUE(award_id, judge_id) pada raw event. Pengecekan aplikasi saja belum cukup untuk double click/race condition.
id_pertandingan, id_babak, dan created_by tidak diikat ke tabel terkait. Bahkan tipe id_pertandingan BIGINT UNSIGNED tidak sama dengan pertandingan.id INT.
app/Http/Usecases/MonitorDisplayUsecase.php Response punya peringatan_merah, tetapi tidak punya peringatan_biru. Tampilan monitor bisa salah/tidak lengkap.
app/Http/Usecases/TimerUsecase.php Jika round/status/time invalid, sistem mengabaikan sebagian input tetapi tetap return success: true. Seharusnya return 422 agar client tahu input ditolak.
app/Http/Controllers/TimerController.php User timer bisa mengirim id_pertandingan tertentu. Usecase tidak mengecek apakah pertandingan tersebut benar-benar playing.
