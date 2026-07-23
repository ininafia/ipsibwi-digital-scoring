Di schema score_windows, kolom yang ada hanya:

match_id
round_id
athlete_red
athlete_blue
technique
opened
opened_at
status


Tidak ada kolom seperti:

athlete ENUM('red', 'blue')

Akibatnya, di JuriUsecase::inputScore(), pencarian window aktif hanya berdasarkan:

match_id
round_id
status = open
technique

Saran: 
Tambahkan di migrasi
ALTER TABLE score_windows
ADD COLUMN athlete ENUM('red', 'blue') NOT NULL AFTER round_id;

ALTER TABLE score_windows
ADD INDEX idx_score_windows_active_full
(match_id, round_id, athlete, technique, status);

Kemudian pada juriusecase inputscore()
$activeWindows = DB::table('score_windows')
    ->where('match_id', $idPertandingan)
    ->where('round_id', $idBabak)
    ->where('athlete', $athlete)
    ->where('status', 'open')
    ->where('technique', $technique)
    ->lockForUpdate()
    ->get();

Saat insert window baru, simpan juga 'athlete' => $athlete,
Dan di resolveGroup(), konsensus sebaiknya dihitung berdasarkan pasangan athlete + technique. bukan technique saja.
Finalisasi pertandingan tidak lagi validasi timer dan ronde

Untuk jenis_kemenangan = angka, tambahkan validasi:

$timerState = Cache::get('current_timer_state_' . $id, [
    'round' => 1,
    'time_remaining' => 120,
    'status' => 'stopped',
]);

if ($jenisKemenangan === 'angka') {
    if (
        (int) $timerState['round'] !== 3 ||
        (int) $timerState['time_remaining'] > 0 ||
        $timerState['status'] !== 'stopped'
    ) {
        DB::rollback();
        return Response::buildErrorService(
            'Kemenangan angka hanya dapat difinalisasi setelah ronde 3 selesai dan timer berhenti.'
        );
    }
}

Untuk disk, undur_diri, teknik, mutlak, atau wmp, boleh selesai sebelum ronde 3, tetapi harus wajib ada:

sudut_pemenang
catatan_finalisasi / alasan
role yang mengesahkan

Beberapa method di PenilaianAtletUsecase.php sudah memakai DB::beginTransaction(), tetapi masih ada return langsung di tengah transaksi tanpa DB::rollback().

Contoh:

return Response::buildErrorService("Tidak ada jatuhan yang bisa dihapus...");

di
app/Http/Usecases/PenilaianAtletUsecase.php baris

Masalah serupa ada di:

addBinaan()      
addTeguran()     
addPeringatan()  
delBinaan()    
delTeguran()  
delPeringatan()  
Dampak

Transaksi bisa menggantung atau koneksi berada pada state transaksi yang tidak bersih. Dalam kondisi request paralel, ini bisa mengganggu update skor/hukuman.
Di deleteScore(), pencarian input terakhir hanya berdasarkan:

match_id
round
judge_id
athlete
status = pending

di
app/Http/Usecases/JuriUsecase.php baris

Tidak ada filter technique.Kalau juri baru saja menekan beberapa tombol untuk sudut yang sama, misalnya pukulan lalu tendangan, tombol hapus bisa menghapus pending terakhir tanpa user tahu teknik mana yang dihapus.




