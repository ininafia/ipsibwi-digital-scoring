Di app/Http/Usecases/PertandinganUsecase.php
Backend mewajibkan salah satu dari:
$resultData['role_pengesah']
$resultData['pengesah']

Tetapi controller app/Http/Controllers/Operator/PertandinganController.php hanya mengirim
'jenis_kemenangan'
'sudut_pemenang'
'catatan_finalisasi'

Di form finalisasi resources/views/Operator/pertandingan/play.blade.php juga tidak ada input role_pengesah atau pengesah.

Dampaknya untuk kemenangan selain angka, misalnya disk, undur_diri, wmp, teknik, atau mutlak, sistem bisa menolak finalisasi meskipun operator sudah memilih pemenang dan mengisi catatan.
Saran perbaikan: kirim otomatis role pengesah dari session atau tetapkan dari backend.
Contoh di controller:

$resultData = [
    'jenis_kemenangan'   => $request->input('jenis_kemenangan'),
    'sudut_pemenang'     => $request->input('sudut_pemenang'),
    'catatan_finalisasi' => $request->input('catatan_finalisasi'),
    'role_pengesah'      => session('role'),
    'pengesah'           => session('user_id'),
];

Atau jika pengesahan harus oleh Ketua/Dewan, buat input eksplisit di form dan validasi role-nya.
score_windows masih rawan ambigu jika satu juri menekan dua kali sebelum juri lain merespons. kode app/Http/Usecases/JuriUsecase.php sudah membuat window baru jika juri yang sama sudah pernah input pada window aktif. Masalahnya, query active window belum punya urutan:
$activeWindows = DB::table('score_windows')
    ->where(...)
    ->lockForUpdate()
    ->get();

Jika ada dua window terbuka untuk sudut dan teknik yang sama, sistem bisa memilih window lama lebih dulu, tergantung urutan database.
Saran : ubah ke
$activeWindows = DB::table('score_windows')
    ->where('match_id', $idPertandingan)
    ->where('round_id', $idBabak)
    ->where('athlete', $athlete)
    ->where('status', 'open')
    ->where('technique', $technique)
    ->orderByDesc('opened_at')
    ->lockForUpdate()
    ->get();

Jika juri menghapus input, window lama tetap open
Di app/Http/Usecases/JuriUsecase.php  Di deleteScore(), event diubah menjadi deleted.

Tetapi score_windows terkait tidak ditutup atau dibatalkan.

Dampaknya bisa berbahaya:

Juri 1 input tendangan biru.
Juri 1 hapus input tersebut.
Window masih open.
Juri 2 input tendangan biru.
Juri 2 bisa masuk ke window lama yang sebenarnya sudah tidak valid.

Lebih berbahaya lagi, inputCount JuriUsecase.php menghitung semua event di window tanpa filter status:

$inputCount = DB::table('score_events')
    ->where('window_id', $targetWindowId)
    ->count();


Artinya event deleted juga bisa ikut terhitung.

Saran perbaikan
Pertama, hitung hanya input pending dari juri berbeda:
$inputCount = DB::table('score_events')
    ->where('window_id', $targetWindowId)
    ->where('status', 'pending')
    ->distinct('judge_id')
    ->count('judge_id');

Kedua, saat delete score, jika tidak ada lagi event pending di window tersebut, tutup window:

$remainingPending = DB::table('score_events')
    ->where('window_id', $lastInput->window_id)
    ->where('status', 'pending')
    ->count();

if ($remainingPending === 0) {
    DB::table('score_windows')
        ->where('id', $lastInput->window_id)
        ->update([
            'status' => 'expired',
            'opened' => 0,
            'close_at' => microtime(true),
        ]);
}
Di app/Http/Usecases/PenilaianAtletUsecase.php Method validateDewanAssignment() hanya mengecek apakah ada dewan pada pertandingan:

->where('id_pertandingan', $id_pertandingan)
->where('id_role', 3)
->exists();

Belum mengecek apakah akun dewan yang sedang login adalah dewan yang ditugaskan.

Dampak: jika ada banyak akun dewan, dewan A bisa memberi hukuman pada pertandingan yang seharusnya ditangani dewan B, selama tahu id_pertandingan.

Saran: setelah ada data_petugas.id_user, ubah validasi menjadi:

$dewanAssigned = DB::table('petugas_pertandingan')
    ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
    ->where('petugas_pertandingan.id_pertandingan', $id_pertandingan)
    ->where('petugas_pertandingan.id_role', 3)
    ->where('data_petugas.id_user', session('user_id'))
    ->exists();
Timer sudah hanya bisa mengubah pertandingan yang statusnya playing.
Namun jika ada banyak gelanggang/pertandingan paralel, akun timer dengan role 4 masih bisa mengirim id_pertandingan lain yang juga sedang playing.
Saran: sama seperti juri/dewan, timer perlu diikat ke assignment petugas
riwayat_hukuman baru punya foreign key ke pertandingan, belum ke babak dan user
Login masih membedakan Username tidak ditemukan dan Password salah. Saran pesan error hanya : Maaf username atau password salah
