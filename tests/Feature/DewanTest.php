<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class DewanTest extends TestCase
{
    use DatabaseTransactions;

    protected $dewanUser;
    protected $nonDewanUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan user Dewan (Role 3)
        $this->dewanUser = User::create([
            'username' => 'dewan_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 3, // Role Dewan
            'is_active' => 1,
        ]);

        // 2. Siapkan user Non-Dewan (Role 2 - Ketua)
        $this->nonDewanUser = User::create([
            'username' => 'ketua_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 2, // Role Ketua
            'is_active' => 1,
        ]);
    }

    /**
     * Helper untuk mensimulasikan login sebagai Dewan
     */
    protected function loginAsDewan()
    {
        session([
            'user_id' => $this->dewanUser->id,
            'role' => $this->dewanUser->access_type,
            'username' => $this->dewanUser->username,
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper untuk mensimulasikan login sebagai Non-Dewan
     */
    protected function loginAsNonDewan()
    {
        session([
            'user_id' => $this->nonDewanUser->id,
            'role' => $this->nonDewanUser->access_type,
            'username' => $this->nonDewanUser->username,
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper untuk membuat data pertandingan dummy beserta penugasan Dewan
     */
    private function createDummyMatchWithDewan(string $status = 'playing'): int
    {
        $matchId = DB::table('pertandingan')->insertGetId([
            'nomor' => 1,
            'partai' => rand(1000, 999999),
            'gelanggang' => 'A',
            'kelas' => 'A',
            'golongan' => 'dewasa',
            'jenis_kelamin' => 'putra',
            'sudut_merah' => 'Atlet Merah Test',
            'kontingen_merah' => 'Kontingen Merah',
            'sudut_biru' => 'Atlet Biru Test',
            'kontingen_biru' => 'Kontingen Biru',
            'status' => $status,
            'created_by' => $this->dewanUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $petugasId = DB::table('data_petugas')->insertGetId([
            'nama' => 'Dewan Petugas Test'
        ]);

        DB::table('petugas_pertandingan')->insert([
            'id_pertandingan' => $matchId,
            'id_petugas' => $petugasId,
            'id_role' => 3,
            'posisi' => null
        ]);

        return $matchId;
    }

    /**
     * Helper untuk menambahkan skor awal (jatuhan, binaan, teguran, peringatan > 0)
     */
    private function createInitialSkor(int $matchId)
    {
        DB::table('skor_pertandingan')->insert([
            'id_pertandingan' => $matchId,
            'skor_biru' => 10,
            'skor_merah' => 10,
            'jatuhan_biru' => 1,
            'jatuhan_merah' => 1,
            'binaan_biru' => 1,
            'binaan_merah' => 1,
            'teguran_biru' => 1,
            'teguran_merah' => 1,
            'peringatan_biru' => 1,
            'peringatan_merah' => 1,
            'updated_at' => now(),
        ]);
    }

    // ==========================================
    // TC-DW-01 s/d TC-DW-03: DASHBOARD TESTS
    // ==========================================

    /**
     * TC-DW-01: Mengakses Dashboard tanpa login
     * Expected Result: Pengguna diarahkan ke halaman login Dewan
     */
    public function test_tc_dw_01_mengakses_dashboard_tanpa_login()
    {
        session()->flush();
        $response = $this->get('/dewan/dashboard');
        $response->assertStatus(302);
    }

    /**
     * TC-DW-02: Mengakses Dashboard dengan role selain Dewan
     * Expected Result: Sistem menampilkan error 403 Forbidden (Akses ditolak)
     */
    public function test_tc_dw_02_mengakses_dashboard_dengan_role_selain_dewan()
    {
        $response = $this->loginAsNonDewan()->get('/dewan/dashboard');
        $response->assertStatus(403);
    }

    /**
     * TC-DW-03: Mengakses Dashboard dengan akun Dewan
     * Expected Result: Halaman Dashboard Dewan berhasil ditampilkan (HTTP 200)
     */
    public function test_tc_dw_03_mengakses_dashboard_dengan_akun_dewan()
    {
        $response = $this->loginAsDewan()->get('/dewan/dashboard');
        $response->assertStatus(200);
    }

    // ==========================================
    // TC-DW-04 s/d TC-DW-14: PENILAIAN ATLET TESTS
    // ==========================================

    /**
     * TC-DW-04: Mengakses halaman Penilaian Atlet
     * Expected Result: Halaman Penilaian Atlet yang sedang berlangsung berhasil dimuat (HTTP 200)
     */
    public function test_tc_dw_04_mengakses_halaman_penilaian_atlet()
    {
        $response = $this->loginAsDewan()->get('/dewan/penilaian-atlet');
        $response->assertStatus(200);
    }

    /**
     * TC-DW-05: Menambahkan Jatuhan (Valid)
     * Expected Result: Skor jatuhan berhasil ditambahkan, mengembalikan JSON success
     */
    public function test_tc_dw_05_menambahkan_jatuhan_valid()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/jatuhan', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-06: Menambahkan Jatuhan (Tidak Valid/Data Kosong)
     * Expected Result: Sistem menolak request (Validation Error 422)
     */
    public function test_tc_dw_06_menambahkan_jatuhan_tidak_valid()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/jatuhan', [
            'id_pertandingan' => '',
            'id_babak' => '',
            'sudut' => 'invalid_sudut'
        ]);

        $response->assertStatus(422);
    }

    /**
     * TC-DW-07: Menghapus Jatuhan
     * Expected Result: Skor jatuhan berhasil dikurangi
     */
    public function test_tc_dw_07_menghapus_jatuhan()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');
        $this->createInitialSkor($matchId);

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/del-jatuhan', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'biru'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-08: Menambahkan Binaan
     * Expected Result: Poin binaan ditambahkan
     */
    public function test_tc_dw_08_menambahkan_binaan()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/binaan', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-09: Menghapus Binaan
     * Expected Result: Poin binaan dibatalkan/dikurangi
     */
    public function test_tc_dw_09_menghapus_binaan()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');
        $this->createInitialSkor($matchId);

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/del-binaan', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-10: Menambahkan Teguran
     * Expected Result: Poin teguran ditambahkan, nilai validasi berhasil
     */
    public function test_tc_dw_10_menambahkan_teguran()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/teguran', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'biru'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-11: Menghapus Teguran
     * Expected Result: Poin teguran dibatalkan/dikurangi
     */
    public function test_tc_dw_11_menghapus_teguran()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');
        $this->createInitialSkor($matchId);

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/del-teguran', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'biru'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-12: Menambahkan Peringatan
     * Expected Result: Poin peringatan ditambahkan
     */
    public function test_tc_dw_12_menambahkan_peringatan()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/peringatan', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-13: Menghapus Peringatan
     * Expected Result: Poin peringatan dibatalkan/dikurangi
     */
    public function test_tc_dw_13_menghapus_peringatan()
    {
        $matchId = $this->createDummyMatchWithDewan('playing');
        $this->createInitialSkor($matchId);

        $response = $this->loginAsDewan()->postJson('/dewan/penilaian-atlet/del-peringatan', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /**
     * TC-DW-14: Mendapatkan Data Penilaian Atlet
     * Expected Result: Mengembalikan struktur JSON berisi skor terkini dari pertandingan
     */
    public function test_tc_dw_14_mendapatkan_data_penilaian_atlet()
    {
        $response = $this->loginAsDewan()->getJson('/dewan/penilaian-atlet/data');
        $response->assertStatus(200);
    }

    // ==========================================
    // TC-DW-15 s/d TC-DW-19: PETUGAS PERTANDINGAN TESTS
    // ==========================================

    /**
     * TC-DW-15: Mengakses halaman Petugas Pertandingan
     * Expected Result: Halaman list petugas pertandingan ditampilkan
     */
    public function test_tc_dw_15_mengakses_halaman_petugas_pertandingan()
    {
        $response = $this->loginAsDewan()->get('/dewan/petugas');
        $response->assertStatus(200);
    }

    /**
     * TC-DW-16: Mengakses form Tambah Petugas
     * Expected Result: Halaman form penugasan petugas dimuat
     */
    public function test_tc_dw_16_mengakses_form_tambah_petugas()
    {
        $response = $this->loginAsDewan()->get('/dewan/petugas/add');
        $response->assertStatus(200);
    }

    /**
     * TC-DW-17: Menyimpan data penugasan petugas
     * Expected Result: Petugas berhasil ditugaskan dan diarahkan kembali dengan pesan sukses
     */
    public function test_tc_dw_17_menyimpan_data_penugasan_petugas()
    {
        $ketuaId = DB::table('data_petugas')->insertGetId(['nama' => 'Ketua Test']);
        $dewanId = DB::table('data_petugas')->insertGetId(['nama' => 'Dewan Test']);
        $wasitId = DB::table('data_petugas')->insertGetId(['nama' => 'Wasit Test']);
        $juri1Id = DB::table('data_petugas')->insertGetId(['nama' => 'Juri 1 Test']);
        $juri2Id = DB::table('data_petugas')->insertGetId(['nama' => 'Juri 2 Test']);
        $juri3Id = DB::table('data_petugas')->insertGetId(['nama' => 'Juri 3 Test']);

        $matchId = $this->createDummyMatchWithDewan('waiting');

        $response = $this->loginAsDewan()->post('/dewan/petugas/store', [
            'id_pertandingan' => $matchId,
            'ketua' => $ketuaId,
            'dewan' => $dewanId,
            'wasit' => $wasitId,
            'juri1' => $juri1Id,
            'juri2' => $juri2Id,
            'juri3' => $juri3Id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('dewan.petugas'));

        $this->assertDatabaseHas('petugas_pertandingan', [
            'id_pertandingan' => $matchId,
            'id_petugas' => $dewanId,
            'id_role' => 3,
        ]);
    }

    /**
     * TC-DW-18: Menjalankan petugas (Run Petugas)
     * Expected Result: Status pertandingan diubah menjadi 'playing', diarahkan ke halaman penilaian
     */
    public function test_tc_dw_18_menjalankan_petugas_run_petugas()
    {
        $matchId = $this->createDummyMatchWithDewan('waiting');

        $response = $this->loginAsDewan()->post("/dewan/petugas/{$matchId}/run");

        $response->assertStatus(302);
        $response->assertRedirect(route('dewan.penilaian'));

        $this->assertDatabaseHas('pertandingan', [
            'id' => $matchId,
            'status' => 'playing'
        ]);
    }

    /**
     * TC-DW-19: Menghapus data penugasan
     * Expected Result: Penugasan terhapus, diarahkan kembali ke daftar petugas
     */
    public function test_tc_dw_19_menghapus_data_penugasan()
    {
        $matchId = $this->createDummyMatchWithDewan('waiting');

        $response = $this->loginAsDewan()->delete("/dewan/petugas/{$matchId}");

        $response->assertStatus(302);
        $response->assertRedirect(route('dewan.petugas'));

        $this->assertDatabaseMissing('petugas_pertandingan', [
            'id_pertandingan' => $matchId
        ]);
    }
}
