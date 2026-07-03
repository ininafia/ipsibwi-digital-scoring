<?php

namespace App\Entities;

class DatabaseEntity
{
    /**
     * Database Connection
     */
    const SQL_READ = 'mysql';

    /**
     * Table
     */
    const USER = 'users';

    const ROLE = 'roles';

    const DATA_PETUGAS = 'data_petugas';

    const KONTINGEN = 'kontingen';

    const ATLET = 'atlet';

    const PERTANDINGAN = 'pertandingan';

    const PETUGAS_PERTANDINGAN = 'petugas_pertandingan';

    const BABAK = 'babak';

    const KATEGORI_NILAI = 'kategori_nilai';

    const NILAI_PERTANDINGAN = 'nilai_pertandingan';

    const AKURASI_JURI = 'akurasi_juri';
}