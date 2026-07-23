<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class OperatorTest extends TestCase
{
    // Gunakan DatabaseTransactions agar semua perubahan DB di-rollback setelah test selesai
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
        
        // Simulasikan session login
        session([
            'user_id' => $this->operatorUser->id,
            'role' => $this->operatorUser->access_type,
            'username' => $this->operatorUser->username,
            'is_logged_in' => true
        ]);
    }

    // TC_OP_001: Akses Dashboard Operator
    public function test_operator_can_access_dashboard()
    {
        $response = $this->get('/operator/tanding/dashboard');
        $response->assertStatus(200);
    }

    // TC_OP_002: Menampilkan Data Monitor
    public function test_operator_can_get_monitor_data()
    {
        $response = $this->get('/operator/monitor-display/data');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data'
                 ]);
    }

    // TC_OP_003: Membuat Jadwal Pertandingan
    public function test_operator_can_create_jadwal()
    {
        // Setup data dummy untuk referensi
        $partaiId = DB::table('partai')->insertGetId([
            'nama_partai' => 'Partai Test ' . uniqid(),
            'kategori' => 'Tanding',
            'kelas' => 'A'
        ]);

        $sudutMerahId = DB::table('atlet')->insertGetId(['nama' => 'Atlet Merah Test']);
        $sudutBiruId = DB::table('atlet')->insertGetId(['nama' => 'Atlet Biru Test']);

        $response = $this->post('/operator/tanding/jadwal', [
            'id_partai' => $partaiId,
            'sudut_merah' => $sudutMerahId,
            'sudut_biru' => $sudutBiruId,
        ]);

        $response->assertStatus(200); // Controller biasanya meresponse JSON sukses untuk AJAX atau redirect

        $this->assertDatabaseHas('pertandingan', [
            'id_partai' => $partaiId,
            'sudut_merah' => $sudutMerahId,
            'sudut_biru' => $sudutBiruId
        ]);
    }

    // TC_OP_004: Mengubah Jadwal Pertandingan
    public function test_operator_can_update_jadwal()
    {
        $partaiId = DB::table('partai')->insertGetId([
            'nama_partai' => 'Partai Test',
            'kategori' => 'Tanding',
            'kelas' => 'A'
        ]);
        $sudutMerahId = DB::table('atlet')->insertGetId(['nama' => 'Atlet Merah Test']);
        $sudutBiruId = DB::table('atlet')->insertGetId(['nama' => 'Atlet Biru Test']);

        $pertandinganId = DB::table('pertandingan')->insertGetId([
            'id_partai' => $partaiId,
            'sudut_merah' => $sudutMerahId,
            'sudut_biru' => $sudutBiruId,
            'status' => 'waiting'
        ]);

        $sudutBiruBaruId = DB::table('atlet')->insertGetId(['nama' => 'Atlet Biru Baru']);

        $response = $this->put("/operator/tanding/jadwal/{$pertandinganId}/update", [
            'id_partai' => $partaiId,
            'sudut_merah' => $sudutMerahId,
            'sudut_biru' => $sudutBiruBaruId, // Data diupdate
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('pertandingan', [
            'id' => $pertandinganId,
            'sudut_biru' => $sudutBiruBaruId
        ]);
    }

    // TC_OP_005: Menghapus Jadwal Pertandingan
    public function test_operator_can_delete_jadwal()
    {
        $pertandinganId = DB::table('pertandingan')->insertGetId([
            'id_partai' => 1,
            'sudut_merah' => 1,
            'sudut_biru' => 2,
            'status' => 'waiting'
        ]);

        $response = $this->delete("/operator/tanding/jadwal/{$pertandinganId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('pertandingan', [
            'id' => $pertandinganId
        ]);
    }

    // TC_OP_006: Mengubah Status Daftar Tunggu
    public function test_operator_can_update_waiting_list_status()
    {
        $pertandinganId = DB::table('pertandingan')->insertGetId([
            'id_partai' => 1,
            'sudut_merah' => 1,
            'sudut_biru' => 2,
            'status' => 'waiting'
        ]);

        $response = $this->patch("/operator/tanding/waiting-list/{$pertandinganId}/status", [
            'status' => 'playing'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('pertandingan', [
            'id' => $pertandinganId,
            'status' => 'playing'
        ]);
    }

    // TC_OP_007: Memulai Pertandingan (Play)
    public function test_operator_can_play_match()
    {
        $pertandinganId = DB::table('pertandingan')->insertGetId([
            'id_partai' => 1,
            'sudut_merah' => 1,
            'sudut_biru' => 2,
            'status' => 'waiting'
        ]);

        // Mock cache untuk timer (biasanya set cache)
        $response = $this->get("/operator/pertandingan/{$pertandinganId}/play");
        
        // Harapannya ini redirect atau load view operator-display
        $response->assertStatus(200);
    }

    // TC_OP_010: Mereset Password Akun
    public function test_operator_can_reset_password()
    {
        $user = User::create([
            'username' => 'juri_reset_test',
            'password' => Hash::make('oldpassword'),
            'access_type' => 5,
        ]);

        $response = $this->post('/operator/akun/reset-password', [
            'id' => $user->id,
            'password' => 'newpassword123'
        ]);

        $response->assertStatus(200);
        
        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    // TC_OP_011: Tambah Data Petugas
    public function test_operator_can_store_petugas()
    {
        $response = $this->post('/operator/petugas/store', [
            'nama' => 'Juri Testing OP',
            'tugas' => 'Juri'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('data_petugas', [
            'nama' => 'Juri Testing OP',
            'tugas' => 'Juri'
        ]);
    }

    // TC_OP_012: Edit Data Petugas
    public function test_operator_can_update_petugas()
    {
        $petugasId = DB::table('data_petugas')->insertGetId([
            'nama' => 'Old Name',
            'tugas' => 'Wasit'
        ]);

        $response = $this->put("/operator/petugas/{$petugasId}/update", [
            'nama' => 'New Name',
            'tugas' => 'Wasit'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('data_petugas', [
            'id' => $petugasId,
            'nama' => 'New Name'
        ]);
    }

    // TC_OP_013: Hapus Data Petugas
    public function test_operator_can_delete_petugas()
    {
        $petugasId = DB::table('data_petugas')->insertGetId([
            'nama' => 'Delete Me',
            'tugas' => 'Juri'
        ]);

        $response = $this->delete("/operator/petugas/{$petugasId}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('data_petugas', [
            'id' => $petugasId
        ]);
    }
}
