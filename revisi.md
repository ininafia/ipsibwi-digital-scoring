routes/web.php. Route ini semuanya berada dalam middleware role:5:

Route::get('/juri1', 'index')->name('juri1');
Route::get('/juri2', 'index')->name('juri2');
Route::get('/juri3', 'index')->name('juri3');

Jadi setiap akun dengan role 5 bisa membuka tiga halaman tersebut.
Perbaikan : setelah user login, sistem harus menentukan posisi juri sebenarnya dari tabel petugas_pertandingan, lalu hanya izinkan akses ke posisi tersebut.

Di AuthController::doLogin(), setelah password benar, sistem langsung menyimpan session.  Tambahkan $request->session()->regenerate();
Saat logout gunakan $request->session()->invalidate();
$request->session()->regenerateToken();
Route logout jangan pakai GET Route::get('/logout', 'doLogout')->name('logout'). Gunakan post
Di route /operator/monitor-display/data hanya mengecek session('user_id'), bukan role spesifik. Akibatnya semua user login bisa membaca data pertandingan aktif. Sesuaikan hanya bisa diakses role yang sesuai
Di modal finalisasi, sudut_pemenang disimpan di hidden input

<input type="hidden" name="sudut_pemenang" id="hidSudut">

Backend finalizeMatch() langsung menyimpan

'winner_corner' => $resultData['sudut_pemenang'] ?? null,
'winning_method' => $resultData['jenis_kemenangan'] ?? null,

Tidak ada validasi server-side bahwa

sudut pemenang sesuai skor akhir
jenis kemenangan termasuk daftar valid
pertandingan memang sedang playing
tidak ada pending score
timer sudah berhenti
finalisasi belum pernah dilakukan.

Dampaknya operator bisa memanipulasi request dan menyimpan pemenang yang tidak sesuai skor.

Perbaikan : backend harus menghitung ulang pemenang dari database, bukan percaya hidden input
Controller membaca 'nama_pemenang' => $request->input('nama_pemenang')
Tetapi di form finalisasi tidak terlihat input nama_pemenang.
finalizeMatch() langsung mengambil skor dari skor_pertandingan

$scoreRecord = DB::table('skor_pertandingan')->where('id_pertandingan', $id)->first();

Tetapi tidak ada proses untuk memastikan semua score_events berstatus final.jika masih ada input juri pending saat operator finalisasi, skor akhir bisa belum mencerminkan kondisi sebenarnya.sebelum finalisasi, jalankan resolusi semua pending event untuk pertandingan tersebut, atau tolak finalisasi jika masih ada pending.
Dewan bisa menambah/menghapus jatuhan,binaan,teguran,peringatan. Namun sistem hanya mengubah angka agregat di skor_pertandingan.setelah pertandingan selesai,  tapi tidak bisa ditelusuri siapa menambahkan hukuman, kapan hukuman ditambahkan, siapa menghapus,alasan penghapusan, ronde berapa kejadian terjadi.
