tolong buatkan script untuk automasi full alur bisnisnya :

dimana pada project digital scoring ini mempunyai beberapa role, diantaranya :

1.	Operator, bertugas: input jadwal pertandingan, input data petugas pertandingan, menjalankan pertandingan, dan memfinalisasi pertandingan
2.	Dewan , bertugas : menambahkan data petugas yang sudah tersimpan dari inputan role operator tadi. Lalu menjalankan petugas pertandingan yang akan di tugaskan pada partai tertentu. Selain itu dewan juga bisa memberikan skor dan hukuman atlet.
3.	Timer, bertugas : mengatur babak, dan menjalankan waktu pertandingan.
4.	Juri, bertugas : menilai serangan teknik atlet.
5.	Ketua pertandingan, bertugas memantau monitor penilaian pertandingan dan menyimpan persentase evaluasi juri dari metode perhitungan akurasi juri.
Untuk runtunan pengerjaannya sebagai berikut :
1.	Operator menginput jadwal pertandingan dan menginput data petugas pertandingan. Setelah data tersimpan
2.	Pada role dewan menambahkan siapa saja yang menjadi petugas pertandingan, lalu menugaskan petugas yang akan di tugaskan pada partai yang akan menjalankan pertandingan.
3.	Lalu pada role timer, tim timer mengatur babak 
4.	Di sisi lain pada role operator, operator menjalankan salah satu partai pada jadwal pertandingan di halaman waiting list.
5.	Otomatis pada role juri, di halaman juri akan muncul data pertandingan yang akan bermain, begitupun juga pada role dewan sudah siap pada halaman penilaian, dan pada role ketua sudah siap pada halaman monitor penilaian pertandinganyang akan bermain. 
6.	Setelah semua role sudah siap tim timer akan menjalankan waktu pertandingan. Semua role akan otomatis terhubung dan berjalan ketika timer menjalankan waktu pertandingan.
7.	Ketika ada masalah di Tengah jalan, maka tim timer akan menjeda terlebih dahulu waktunya, maka semua role otomatis ikut terjeda. Setelah di jalankan lagi semua role akan berjalan sesuai waktu pertandingan yang sedang berjalan.
8.	Lalu operator memfinalisasi pertandingan, dimana pada sudut pemenang sudah terlihat, dan operator memilih jenis kemenangan, dan di simpan data tersebut. 
9.	Maka data tersebut akan tersimpan pada halaman finished.
10.	Dan langsung otomatis pada halaman persentase juri di role ketua akan muncul dan tertambah otomatis data yang sudah di perhitungkan dari metode akurasi evaluasi juri tersebut.


JIka tedapat error / terdapat fitur yang belum ada tolong beri log nya