<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class KetuaTest extends TestCase
{
    use DatabaseTransactions;

    protected $ketuaUser;
    protected $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan User Ketua Pertandingan (Role 2)
        $this->ketuaUser = User::create([
            'username' => 'ketua_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 2, // Role Ketua
            'is_active' => 1,
        ]);

        // 2. Siapkan User Operator (Role 1)
        $this->operatorUser = User::create([
            'username' => 'operator_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 1, // Role Operator
            'is_active' => 1,
        ]);
    }

    /**
     * Helper login sebagai Ketua Pertandingan
     */
    protected function loginAsKetua()
    {
        session([
            'user_id' => $this->ketuaUser->id,
            'role' => $this->ketuaUser->access_type,
            'username' => $this->ketuaUser->username,
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper login sebagai Operator (Non-Ketua)
     */
    protected function loginAsOperator()
    {
        session([
            'user_id' => $this->operatorUser->id,
            'role' => $this->operatorUser->access_type,
            'username' => $this->operatorUser->username,
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper untuk membuat data pertandingan dummy
     */
    private function createDummyMatch(string $status = 'playing'): int
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
            'created_by' => $this->ketuaUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Helper membuat pertandingan dummy dengan data akurasi_juri lengkap
     */
    private function createDummyMatchWithAkurasi(string $status = 'finished'): array
    {
        $matchId = $this->createDummyMatch($status);

        $petugasId = DB::table('data_petugas')->insertGetId([
            'nama' => 'Juri Akurasi Test',
            'tugas' => 'Juri'
        ]);

        $ppId = DB::table('petugas_pertandingan')->insertGetId([
            'id_pertandingan' => $matchId,
            'id_petugas' => $petugasId,
            'id_role' => 5,
            'posisi' => 'juri_1'
        ]);

        DB::table('akurasi_juri')->insert([
            'id_pertandingan' => $matchId,
            'id_petugas_pertandingan' => $ppId,
            'total_input' => 10,
            'total_nilai_sah' => 8,
            'total_nilai_tidak_sah' => 2,
            'persentase_akurasi' => 80.00,
            'tanggal_dihitung' => now(),
        ]);

        return ['match_id' => $matchId, 'petugas_id' => $petugasId, 'pp_id' => $ppId];
    }

    // ==========================================
    // TC-KP-01 s/d TC-KP-03: DASHBOARD KETUA TESTS
    // ==========================================

    /**
     * TC-KP-01: Akses halaman Dashboard Ketua tanpa login
     * Expected Result: Sistem me-redirect pengguna ke /login/ketua
     */
    public function test_tc_kp_01_akses_dashboard_ketua_tanpa_login()
    {
        session()->flush();
        $response = $this->get('/ketua/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * TC-KP-02: Akses halaman Ketua menggunakan akun dengan role selain Ketua
     * Expected Result: Akses ditolak, sistem mengembalikan HTTP 403
     */
    public function test_tc_kp_02_akses_ketua_role_selain_ketua()
    {
        $response = $this->loginAsOperator()->get('/ketua/dashboard');
        $response->assertStatus(403);
    }

    /**
     * TC-KP-03: Akses halaman Dashboard Ketua menggunakan akun Ketua
     * Expected Result: Halaman Dashboard Ketua berhasil dimuat (HTTP 200)
     */
    public function test_tc_kp_03_akses_dashboard_ketua_dengan_akun_ketua()
    {
        $response = $this->loginAsKetua()->get('/ketua/dashboard');
        $response->assertStatus(200);
    }

    // ==========================================
    // TC-KP-04 s/d TC-KP-06: MONITOR KETUA TESTS
    // ==========================================

    /**
     * TC-KP-04: Akses halaman Monitor Ketua
     * Expected Result: Halaman Monitor (tampilan layar lebar/TV) berhasil dimuat (HTTP 200)
     */
    public function test_tc_kp_04_akses_halaman_monitor_ketua()
    {
        $response = $this->loginAsKetua()->get('/ketua/monitor');
        $response->assertStatus(200);
    }

    /**
     * TC-KP-05: Memuat Data Monitor (/ketua/monitor/data) saat tidak ada pertandingan aktif
     * Expected Result: Sistem mengembalikan JSON: {'success': false, 'message': 'Tidak ada pertandingan aktif'}
     */
    public function test_tc_kp_05_memuat_data_monitor_saat_tidak_ada_pertandingan_aktif()
    {
        // Kosongkan semua pertandingan aktif
        DB::table('pertandingan')->whereIn('status', ['playing', 'finished', 'final'])->update(['deleted_at' => now()]);

        $response = $this->loginAsKetua()->get('/ketua/monitor/data');
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Tidak ada pertandingan aktif'
                 ]);
    }

    /**
     * TC-KP-06: Memuat Data Monitor (/ketua/monitor/data) saat ada pertandingan berjalan
     * Expected Result: Mengembalikan JSON lengkap berisi match, timer, juri_scores, round_totals, penalties_formatted, grand_total, event_history, award_history (HTTP 200)
     */
    public function test_tc_kp_06_memuat_data_monitor_saat_ada_pertandingan_berjalan()
    {
        $matchId = $this->createDummyMatch('playing');

        Cache::put('current_timer_state_' . $matchId, [
            'round' => 2,
            'time_remaining' => 90,
            'status' => 'playing'
        ]);

        $response = $this->loginAsKetua()->get('/ketua/monitor/data');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'match',
                     'timer',
                     'juri_scores',
                     'round_totals',
                     'penalties_formatted',
                     'grand_total',
                     'event_history',
                     'award_history'
                 ]);
    }

    // ==========================================
    // TC-KP-07: LOG ACTIVITY JURI TEST
    // ==========================================

    /**
     * TC-KP-07: Akses halaman Log Activity Juri
     * Expected Result: Halaman termuat dengan baik (HTTP 200)
     */
    public function test_tc_kp_07_akses_halaman_log_activity_juri()
    {
        $response = $this->loginAsKetua()->get('/ketua/log-juri');
        $response->assertStatus(200);
    }

    // ==========================================
    // TC-KP-08 s/d TC-KP-11: AKURASI JURI TESTS
    // ==========================================

    /**
     * TC-KP-08: Akses halaman Persentase / Akurasi Juri
     * Expected Result: Halaman termuat (HTTP 200), menampilkan agregasi data akurasi per juri dan statistik total
     */
    public function test_tc_kp_08_akses_halaman_persentase_akurasi_juri()
    {
        $response = $this->loginAsKetua()->get('/ketua/persentase-juri');
        $response->assertStatus(200);
    }

    /**
     * TC-KP-09: Download PDF Akurasi Juri Seluruh Pertandingan
     * Expected Result: File PDF di-download, HTTP 200
     */
    public function test_tc_kp_09_download_pdf_akurasi_juri_seluruh_pertandingan()
    {
        $this->createDummyMatchWithAkurasi('finished');

        $response = $this->loginAsKetua()->get('/ketua/persentase-juri/export-all?type=babak');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * TC-KP-10: Download PDF Akurasi Juri pada Pertandingan Spesifik
     * Expected Result: File PDF di-download memuat tabel juri pertandingan tersebut
     */
    public function test_tc_kp_10_download_pdf_akurasi_juri_pertandingan_spesifik()
    {
        $data = $this->createDummyMatchWithAkurasi('finished');

        $response = $this->loginAsKetua()->get('/ketua/persentase-juri/export/' . $data['match_id']);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * TC-KP-11: Download PDF Akurasi Juri pada ID Pertandingan yang Tidak Valid
     * Expected Result: Sistem menggagalkan download dan me-return HTTP 404 dengan pesan "Data pertandingan tidak ditemukan"
     */
    public function test_tc_kp_11_download_pdf_akurasi_juri_id_invalid()
    {
        $response = $this->loginAsKetua()->get('/ketua/persentase-juri/export/999999');

        $response->assertStatus(404);
    }
}
