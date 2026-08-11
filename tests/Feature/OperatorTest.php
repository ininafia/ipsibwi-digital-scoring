<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class OperatorTest extends TestCase
{
    use DatabaseTransactions;

    protected $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan user operator untuk autentikasi
        $this->operatorUser = User::create([
            'username' => 'operator_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 1, // Role Operator
            'is_active' => 1,
        ]);
        
        // Simulasikan session login sebagai Operator
        session([
            'user_id' => $this->operatorUser->id,
            'role' => $this->operatorUser->access_type,
            'username' => $this->operatorUser->username,
            'is_logged_in' => true
        ]);
    }

    /**
     * Helper untuk membuat data pertandingan dummy
     */
    private function createDummyMatch(string $status = 'waiting'): int
    {
        return DB::table('pertandingan')->insertGetId([
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
            'created_by' => $this->operatorUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * TC-OP-01: Akses Dashboard Operator
     * Expected Result: Halaman dashboard berhasil dimuat (HTTP 200)
     */
    public function test_tc_op_01_akses_dashboard_operator()
    {
        $response = $this->get('/operator/tanding/dashboard');
        $response->assertStatus(200);
    }

    /**
     * TC-OP-02: Menampilkan Data Monitor
     * Expected Result: Menerima response JSON berisi data pertandingan
     */
    public function test_tc_op_02_menampilkan_data_monitor()
    {
        $response = $this->get('/operator/monitor-display/data');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success'
                 ]);
    }

    /**
     * TC-OP-03: Membuat Jadwal Pertandingan
     * Expected Result: Data jadwal tersimpan, redirect ke halaman list jadwal
     */
    public function test_tc_op_03_membuat_jadwal_pertandingan()
    {
        $partaiNumber = rand(1000, 999999);
        $data = [
            'nomor' => 1,
            'partai' => $partaiNumber,
            'gelanggang' => 'A',
            'kelas' => 'A',
            'golongan' => 'dewasa',
            'jenis_kelamin' => 'putra',
            'sudut_merah' => 'Atlet Merah Baru',
            'kontingen_merah' => 'Kontingen Merah',
            'sudut_biru' => 'Atlet Biru Baru',
            'kontingen_biru' => 'Kontingen Biru',
        ];

        $response = $this->post('/operator/tanding/jadwal', $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('operator.tanding.index'));

        $this->assertDatabaseHas('pertandingan', [
            'partai' => $partaiNumber,
            'sudut_merah' => 'Atlet Merah Baru',
            'sudut_biru' => 'Atlet Biru Baru',
        ]);
    }

    /**
     * TC-OP-04: Mengubah Jadwal Pertandingan
     * Expected Result: Data jadwal terupdate di database
     */
    public function test_tc_op_04_mengubah_jadwal_pertandingan()
    {
        $matchId = $this->createDummyMatch('waiting');
        $partaiNumber = rand(1000, 999999);

        $updateData = [
            'nomor' => 2,
            'partai' => $partaiNumber,
            'gelanggang' => 'B',
            'kelas' => 'B',
            'golongan' => 'remaja',
            'jenis_kelamin' => 'putra',
            'sudut_merah' => 'Atlet Merah Updated',
            'kontingen_merah' => 'Kontingen Merah Updated',
            'sudut_biru' => 'Atlet Biru Updated',
            'kontingen_biru' => 'Kontingen Biru Updated',
        ];

        $response = $this->put("/operator/tanding/jadwal/{$matchId}/update", $updateData);

        $response->assertStatus(302);

        $this->assertDatabaseHas('pertandingan', [
            'id' => $matchId,
            'sudut_merah' => 'Atlet Merah Updated',
            'sudut_biru' => 'Atlet Biru Updated',
        ]);
    }

    /**
     * TC-OP-05: Menghapus Jadwal Pertandingan
     * Expected Result: Data jadwal terhapus dari database
     */
    public function test_tc_op_05_menghapus_jadwal_pertandingan()
    {
        $matchId = $this->createDummyMatch('waiting');

        $response = $this->delete("/operator/tanding/jadwal/{$matchId}");
        $response->assertStatus(302);

        $this->assertDatabaseMissing('pertandingan', [
            'id' => $matchId,
            'deleted_at' => null,
        ]);
    }

    /**
     * TC-OP-06: Mengubah Status Daftar Tunggu
     * Expected Result: Status berubah, response sukses
     */
    public function test_tc_op_06_mengubah_status_daftar_tunggu()
    {
        $matchId = $this->createDummyMatch('waiting');

        $response = $this->patch("/operator/tanding/waiting-list/{$matchId}/status", [
            'status' => 'playing'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        $this->assertDatabaseHas('pertandingan', [
            'id' => $matchId,
            'status' => 'playing'
        ]);
    }

    /**
     * TC-OP-07: Memulai Pertandingan (Play)
     * Expected Result: Menginisiasi skor, timer, dan status playing
     */
    public function test_tc_op_07_memulai_pertandingan_play()
    {
        $matchId = $this->createDummyMatch('waiting');

        $response = $this->get("/operator/pertandingan/{$matchId}/play");
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('pertandingan', [
            'id' => $matchId,
            'status' => 'playing'
        ]);
    }

    /**
     * TC-OP-08: Finalisasi Pertandingan
     * Expected Result: Status pertandingan menjadi finished, pemenang ditentukan
     */
    public function test_tc_op_08_finalisasi_pertandingan()
    {
        $matchId = $this->createDummyMatch('playing');

        $response = $this->post("/operator/tanding/{$matchId}/finalisasi", [
            'jenis_kemenangan' => 'teknik',
            'sudut_pemenang' => 'merah',
            'catatan_finalisasi' => 'Menang Teknik R2'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('pertandingan', [
            'id' => $matchId,
            'status' => 'finished',
            'winner_corner' => 'merah',
            'winning_method' => 'teknik'
        ]);
    }

    /**
     * TC-OP-09: Export PDF Pertandingan
     * Expected Result: Mengunduh file PDF hasil pertandingan
     */
    public function test_tc_op_09_export_pdf_pertandingan()
    {
        $matchId = $this->createDummyMatch('finished');

        $response = $this->get("/operator/tanding/finished/{$matchId}/export-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * TC-OP-10: Mereset Password Akun
     * Expected Result: Password pengguna berubah menjadi password baru
     */
    public function test_tc_op_10_mereset_password_akun()
    {
        $targetUser = User::create([
            'username' => 'juri_reset_' . uniqid(),
            'password' => Hash::make('oldpassword'),
            'access_type' => 5,
            'is_active' => 1,
        ]);

        $response = $this->post('/operator/akun/reset-password', [
            'user_id' => $targetUser->id,
            'new_password' => 'newpassword123'
        ]);

        $response->assertStatus(302);

        $updatedUser = User::find($targetUser->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    /**
     * TC-OP-11: Tambah Data Petugas
     * Expected Result: Data petugas baru ditambahkan ke database
     */
    public function test_tc_op_11_tambah_data_petugas()
    {
        $response = $this->post('/operator/petugas/store', [
            'nama' => 'Juri Testing OP'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('data_petugas', [
            'nama' => 'Juri Testing OP'
        ]);
    }

    /**
     * TC-OP-12: Edit Data Petugas
     * Expected Result: Data petugas berubah sesuai input
     */
    public function test_tc_op_12_edit_data_petugas()
    {
        $petugasId = DB::table('data_petugas')->insertGetId([
            'nama' => 'Old Name'
        ]);

        $response = $this->put("/operator/petugas/{$petugasId}/update", [
            'nama' => 'New Name Updated'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('data_petugas', [
            'id' => $petugasId,
            'nama' => 'New Name Updated'
        ]);
    }

    /**
     * TC-OP-13: Hapus Data Petugas
     * Expected Result: Petugas terhapus dari database
     */
    public function test_tc_op_13_hapus_data_petugas()
    {
        $petugasId = DB::table('data_petugas')->insertGetId([
            'nama' => 'Petugas Delete Me'
        ]);

        $response = $this->delete("/operator/petugas/{$petugasId}");

        $response->assertStatus(302);

        $this->assertDatabaseHas('data_petugas', [
            'id' => $petugasId,
        ]);
        
        $petugas = DB::table('data_petugas')->where('id', $petugasId)->first();
        $this->assertNotNull($petugas->deleted_at);
    }

    /**
     * TC-OP-14: Cetak Detail Finished Mengubah Status Menjadi Final
     */
    public function test_tc_op_14_export_pdf_updates_status_to_final()
    {
        $matchId = $this->createDummyMatch('finished');

        $response = $this->get("/operator/tanding/finished/{$matchId}/export-pdf");
        $response->assertStatus(200);

        $this->assertDatabaseHas('pertandingan', [
            'id' => $matchId,
            'status' => 'final'
        ]);
    }

    /**
     * TC-OP-15: Akses Halaman The Final Result
     */
    public function test_tc_op_15_halaman_the_final_result()
    {
        $matchId = $this->createDummyMatch('final');

        $response = $this->get('/operator/tanding?tab=final');
        $response->assertStatus(200)
                 ->assertSee('Hasil Akhir Pertandingan Pencak Silat')
                 ->assertSee('Kategori tanding');
    }
}
