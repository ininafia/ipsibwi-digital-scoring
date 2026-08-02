<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Events\MatchUpdated;

class TimerTest extends TestCase
{
    use DatabaseTransactions;

    protected $timerUser;
    protected $juriUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan user Timer (Role 4)
        $this->timerUser = User::create([
            'username' => 'timer_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 4, // Role Timer
            'is_active' => 1,
        ]);

        // 2. Siapkan user Juri (Role 5)
        $this->juriUser = User::create([
            'username' => 'juri_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 5, // Role Juri
            'is_active' => 1,
        ]);
    }

    /**
     * Helper untuk mensimulasikan login sebagai Timer
     */
    protected function loginAsTimer()
    {
        session([
            'user_id' => $this->timerUser->id,
            'role' => $this->timerUser->access_type,
            'username' => $this->timerUser->username,
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper untuk mensimulasikan login sebagai Juri
     */
    protected function loginAsJuri()
    {
        session([
            'user_id' => $this->juriUser->id,
            'role' => $this->juriUser->access_type,
            'username' => $this->juriUser->username,
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
            'created_by' => $this->timerUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ==========================================
    // TC-TMR-01 s/d TC-TMR-04: DASHBOARD TIMER TESTS
    // ==========================================

    /**
     * TC-TMR-01: Akses halaman Dashboard Timer tanpa login
     * Expected Result: Sistem me-redirect pengguna ke halaman /login/timer
     */
    public function test_tc_tmr_01_akses_dashboard_timer_tanpa_login()
    {
        session()->flush();
        $response = $this->get('/timer');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * TC-TMR-02: Akses halaman Dashboard Timer menggunakan akun dengan role selain Timer (misal: Juri)
     * Expected Result: Akses ditolak, sistem mengembalikan response 403 (Akses ditolak)
     */
    public function test_tc_tmr_02_akses_dashboard_timer_role_selain_timer()
    {
        $response = $this->loginAsJuri()->get('/timer');
        $response->assertStatus(403);
    }

    /**
     * TC-TMR-03: Akses halaman Dashboard Timer saat ada pertandingan yang berstatus playing
     * Expected Result: Halaman Dashboard Timer berhasil dimuat (HTTP 200) dengan membawa data pertandingan yang sedang aktif
     */
    public function test_tc_tmr_03_akses_dashboard_timer_ada_pertandingan_playing()
    {
        $matchId = $this->createDummyMatch('playing');

        $response = $this->loginAsTimer()->get('/timer');
        $response->assertStatus(200);
        $response->assertViewHas('match');
        
        $match = $response->viewData('match');
        $this->assertNotNull($match);
        $this->assertEquals($matchId, $match->id);
    }

    /**
     * TC-TMR-04: Akses halaman Dashboard Timer saat tidak ada pertandingan aktif
     * Expected Result: Halaman Dashboard Timer tetap dimuat (HTTP 200) namun objek $match akan bernilai null
     */
    public function test_tc_tmr_04_akses_dashboard_timer_tidak_ada_pertandingan_aktif()
    {
        // Pastikan tidak ada pertandingan playing
        DB::table('pertandingan')->where('status', 'playing')->update(['status' => 'finished']);

        $response = $this->loginAsTimer()->get('/timer');
        $response->assertStatus(200);
        $response->assertViewHas('match', null);
    }

    // ==========================================
    // TC-TMR-05 s/d TC-TMR-11 & 14: SINKRONISASI TIMER TESTS
    // ==========================================

    /**
     * TC-TMR-05: Mengirim permintaan sinkronisasi Timer (/timer/sync) menggunakan akun selain Timer
     * Expected Result: Sistem menolak request dengan JSON {'error': 'Unauthorized'} (HTTP 403)
     */
    public function test_tc_tmr_05_sinkronisasi_timer_menggunakan_akun_selain_timer()
    {
        $response = $this->loginAsJuri()->postJson('/timer/sync', [
            'id_pertandingan' => 1,
            'status' => 'playing',
            'time_remaining' => 120
        ]);

        $response->assertStatus(403);
    }

    /**
     * TC-TMR-06: Sinkronisasi Timer (/timer/sync) pada ID pertandingan yang tidak sedang playing (Mencegah Bug-6)
     * Expected Result: Sistem menolak request dengan error "Pertandingan tidak ditemukan atau tidak sedang berjalan" (HTTP 403)
     */
    public function test_tc_tmr_06_sinkronisasi_timer_pada_pertandingan_tidak_playing()
    {
        $matchId = $this->createDummyMatch('finished');

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'status' => 'playing',
            'time_remaining' => 120
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'error' => 'Pertandingan tidak ditemukan atau tidak sedang berjalan'
                 ]);
    }

    /**
     * TC-TMR-07: Sinkronisasi Timer (/timer/sync) dengan format round dan time negatif
     * Expected Result: Sistem menolak request dengan JSON {'success': false, 'error': 'Invalid round'} (HTTP 422)
     */
    public function test_tc_tmr_07_sinkronisasi_timer_format_round_dan_time_invalid()
    {
        $matchId = $this->createDummyMatch('playing');

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'round' => 5,
            'time_remaining' => -10
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => 'Invalid round'
                 ]);
    }

    /**
     * TC-TMR-08: Sinkronisasi Timer: Mengubah status dari stopped menjadi playing
     * Expected Result: JSON mengembalikan 'should_broadcast' => true dan Event MatchUpdated terpicu
     */
    public function test_tc_tmr_08_sinkronisasi_timer_mengubah_status_stopped_ke_playing()
    {
        Event::fake();

        $matchId = $this->createDummyMatch('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'status' => 'playing',
            'time_remaining' => 120
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'should_broadcast' => true
                 ]);

        Event::assertDispatched(MatchUpdated::class, function ($event) use ($matchId) {
            return $event->matchId == $matchId;
        });
    }

    /**
     * TC-TMR-09: Sinkronisasi Timer: Berjalan normal tanpa kelipatan 15 detik
     * Expected Result: Cache diperbarui. JSON mengembalikan 'should_broadcast' => false
     */
    public function test_tc_tmr_09_sinkronisasi_timer_berjalan_normal_tanpa_kelipatan_15_detik()
    {
        $matchId = $this->createDummyMatch('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 118,
            'status' => 'playing'
        ]);

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'status' => 'playing',
            'time_remaining' => 117
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'should_broadcast' => false
                 ]);

        $cachedState = Cache::get('current_timer_state_' . $matchId);
        $this->assertEquals(117, $cachedState['time_remaining']);
    }

    /**
     * TC-TMR-10: Sinkronisasi Timer: Berjalan normal pas pada kelipatan 15 detik
     * Expected Result: Cache diperbarui. JSON mengembalikan 'should_broadcast' => true
     */
    public function test_tc_tmr_10_sinkronisasi_timer_berjalan_pas_kelipatan_15_detik()
    {
        $matchId = $this->createDummyMatch('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 91,
            'status' => 'playing'
        ]);

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'status' => 'playing',
            'time_remaining' => 90
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'should_broadcast' => true
                 ]);
    }

    /**
     * TC-TMR-11: Sinkronisasi Timer: Berpindah Ronde (Misal: 1 ke 2)
     * Expected Result: Cache diperbarui. JSON mengembalikan 'should_broadcast' => true
     */
    public function test_tc_tmr_11_sinkronisasi_timer_berpindah_ronde()
    {
        $matchId = $this->createDummyMatch('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'round' => 2,
            'time_remaining' => 120
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'should_broadcast' => true
                 ]);

        $cachedState = Cache::get('current_timer_state_' . $matchId);
        $this->assertEquals(2, $cachedState['round']);
    }

    // ==========================================
    // TC-TMR-12 & 13: STATE TIMER TESTS
    // ==========================================

    /**
     * TC-TMR-12: Mengambil State Timer saat ini (/timer/state)
     * Expected Result: Sistem mengembalikan JSON state dari cache
     */
    public function test_tc_tmr_12_mengambil_state_timer_saat_ini()
    {
        $matchId = $this->createDummyMatch('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 2,
            'time_remaining' => 90,
            'status' => 'playing'
        ]);

        $response = $this->loginAsTimer()->getJson('/timer/state?id_pertandingan=' . $matchId);

        $response->assertStatus(200)
                 ->assertJson([
                     'round' => 2,
                     'time_remaining' => 90,
                     'status' => 'playing'
                 ]);
    }

    /**
     * TC-TMR-13: Mengambil State Timer tanpa ID spesifik
     * Expected Result: Sistem secara otomatis mencari pertandingan yang sedang playing lalu mengembalikan state cache-nya
     */
    public function test_tc_tmr_13_mengambil_state_timer_tanpa_id_spesifik()
    {
        $matchId = $this->createDummyMatch('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 100,
            'status' => 'playing'
        ]);

        $response = $this->loginAsTimer()->getJson('/timer/state');

        $response->assertStatus(200)
                 ->assertJson([
                     'round' => 1,
                     'time_remaining' => 100,
                     'status' => 'playing'
                 ]);
    }

    /**
     * TC-TMR-14: Sinkronisasi Timer dengan status yang tidak dikenal
     * Expected Result: Sistem mengembalikan JSON {'success': false, 'error': 'Invalid status'} (HTTP 422)
     */
    public function test_tc_tmr_14_sinkronisasi_timer_status_tidak_dikenal()
    {
        $matchId = $this->createDummyMatch('playing');

        $response = $this->loginAsTimer()->postJson('/timer/sync', [
            'id_pertandingan' => $matchId,
            'status' => 'unknown_status'
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => 'Invalid status'
                 ]);
    }
}
