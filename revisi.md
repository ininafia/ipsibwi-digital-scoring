Saran alur yang memungkinakan misal ada  2 pukulan dalam waktu kurang dari 3 detik bisa dideteksi sebagai 2 kejadian yang berbeda:
Saat juri menekan tombol:

Sistem cari score_window aktif untuk:
pertandingan yang sama,
ronde yang sama,
atlet yang sama,
teknik yang sama,
status open,
masih dalam rentang 3 detik.
Jika ada window aktif dan juri tersebut belum pernah input di window itu, masukkan input ke window tersebut.
Jika ada window aktif tetapi juri yang sama sudah input di window itu, maka input baru dari juri tersebut harus dianggap sebagai aksi baru, sehingga sistem membuat score_window baru.
Jika jumlah juri berbeda dalam satu window sudah minimal 2, window tersebut langsung diberi status awarded.
Setelah window menjadi awarded, input berikutnya untuk atlet+teknik yang sama boleh membuka window baru, walaupun masih dalam 3 detik.

