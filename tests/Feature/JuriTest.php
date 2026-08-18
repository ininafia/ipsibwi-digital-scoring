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

class JuriTest extends TestCase
{
    use DatabaseTransactions;

    protected $juri1User;
    protected $juri2User;
    protected $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan User Juri 1 (Role 5)
        $this->juri1User = User::create([
            'username' => 'juri1_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 5, // Role Juri
            'is_active' => 1,
        ]);

        // 2. Siapkan User Juri 2 (Role 5)
        $this->juri2User = User::create([
            'username' => 'juri2_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 5, // Role Juri
            'is_active' => 1,
        ]);

        // 3. Siapkan User Operator (Role 1)
        $this->operatorUser = User::create([
            'username' => 'operator_test_' . uniqid(),
            'password' => Hash::make('password123'),
            'access_type' => 1, // Role Operator
            'is_active' => 1,
        ]);

        // Seed master data kategori_nilai if missing
        DB::table('kategori_nilai')->updateOrInsert(['id' => 1], ['delay_max' => 3.0]);
        DB::table('kategori_nilai')->updateOrInsert(['id' => 2], ['delay_max' => 3.0]);
    }

    /**
     * Helper login sebagai Juri 1
     */
    protected function loginAsJuri1()
    {
        session([
            'user_id' => $this->juri1User->id,
            'role' => $this->juri1User->access_type,
            'username' => $this->juri1User->username,
            'juri_position' => 'juri_1',
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper login sebagai Juri 2
     */
    protected function loginAsJuri2()
    {
        session([
            'user_id' => $this->juri2User->id,
            'role' => $this->juri2User->access_type,
            'username' => $this->juri2User->username,
            'juri_position' => 'juri_2',
            'is_logged_in' => true
        ]);
        return $this;
    }

    /**
     * Helper login sebagai Operator
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
     * Helper membuat pertandingan dummy beserta penugasan Juri 1 dan Juri 2
     */
    private function createDummyMatchWithJuris(string $status = 'playing'): int
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
            'created_by' => $this->juri1User->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $petugas1Id = DB::table('data_petugas')->insertGetId([
            'nama' => 'Juri Satu'
        ]);

        $petugas2Id = DB::table('data_petugas')->insertGetId([
            'nama' => 'Juri Dua'
        ]);

        DB::table('petugas_pertandingan')->insert([
            'id_pertandingan' => $matchId,
            'id_petugas' => $petugas1Id,
            'id_role' => 5,
            'posisi' => 'juri_1'
        ]);

        DB::table('petugas_pertandingan')->insert([
            'id_pertandingan' => $matchId,
            'id_petugas' => $petugas2Id,
            'id_role' => 5,
            'posisi' => 'juri_2'
        ]);

        // Default set timer state to playing
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'playing'
        ]);

        return $matchId;
    }

    // ==========================================
    // TC-JR-01 s/d TC-JR-05: AKSES & AUTH TESTS
    // ==========================================

    /**
     * TC-JR-01: Akses halaman UI Juri tanpa login
     * Expected Result: Sistem me-redirect pengguna ke halaman /login/juri
     */
    public function test_tc_jr_01_akses_ui_juri_tanpa_login()
    {
        session()->flush();
        $response = $this->get('/juri1');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * TC-JR-02: Akses halaman UI Juri dengan role selain Juri (Misal: Operator)
     * Expected Result: Sistem mengembalikan response 403 (Akses ditolak)
     */
    public function test_tc_jr_02_akses_ui_juri_role_selain_juri()
    {
        $response = $this->loginAsOperator()->get('/juri1');
        $response->assertStatus(403);
    }

    /**
     * TC-JR-03: Akses Endpoint AJAX (/juri/input-score) tanpa kredensial Juri yang valid
     * Expected Result: Sistem menolak dengan format JSON HTTP 401: {'success': false, 'message': 'Unauthorized'}
     */
    public function test_tc_jr_03_akses_ajax_tanpa_kredensial_juri()
    {
        session()->flush();
        $response = $this->postJson('/juri/input-score', [
            'id_pertandingan' => 1,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Unauthorized'
                 ]);
    }

    /**
     * TC-JR-04: Validasi silang Posisi Juri (Akses URL Juri 1 menggunakan akun Juri 2)
     * Expected Result: Sistem menolak akses dengan HTTP 403 ("Anda login sebagai JURI 2, bukan JURI 1")
     */
    public function test_tc_jr_04_validasi_silang_posisi_juri()
    {
        $response = $this->loginAsJuri2()->get('/juri1');
        $response->assertStatus(403);
    }

    /**
     * TC-JR-05: Akses halaman UI Juri sesuai posisi saat ada pertandingan aktif
     * Expected Result: Halaman Juri termuat (HTTP 200), menampilkan "JURI 1" dan nama petugas
     */
    public function test_tc_jr_05_akses_ui_juri_sesuai_posisi_pertandingan_aktif()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        $response = $this->loginAsJuri1()->get('/juri1');
        $response->assertStatus(200);
        $response->assertSee('JURI 1');
    }

    // ==========================================
    // TC-JR-06 s/d TC-JR-09: INPUT SCORE TESTS
    // ==========================================

    /**
     * TC-JR-06: Juri melakukan input nilai Pukulan saat Timer pause/stop
     * Expected Result: Sistem mengembalikan HTTP 200/400 dengan JSON pesan: "Waktu pertandingan sedang berhenti (Timer pause/stop)"
     */
    public function test_tc_jr_06_input_nilai_saat_timer_pause_stop()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');
        Cache::put('current_timer_state_' . $matchId, [
            'round' => 1,
            'time_remaining' => 60,
            'status' => 'paused'
        ]);

        $response = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'biru',
            'id_kategori_nilai' => 1
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'Waktu pertandingan sedang berhenti (Timer pause/stop)'
        ]);
    }

    /**
     * TC-JR-07: Juri melakukan input nilai Pukulan saat Timer berjalan
     * Expected Result: Data masuk ke tabel score_events dengan status pending, mengembalikan score_event_id, UI mem-broadcast MatchUpdated
     */
    public function test_tc_jr_07_input_nilai_saat_timer_berjalan()
    {
        Event::fake();

        $matchId = $this->createDummyMatchWithJuris('playing');

        $response = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('score_event_id', $responseData['data']);

        $this->assertDatabaseHas('score_events', [
            'id' => $responseData['data']['score_event_id'],
            'match_id' => $matchId,
            'athlete' => 'red',
            'technique' => 'punch',
            'status' => 'pending'
        ]);

        Event::assertDispatched(MatchUpdated::class, function ($event) use ($matchId) {
            return $event->matchId == $matchId;
        });
    }

    /**
     * TC-JR-08: Juri melakukan input nilai tetapi Pertandingan sudah selesai
     * Expected Result: Sistem mengembalikan pesan: "Pertandingan tidak sedang berlangsung"
     */
    public function test_tc_jr_08_input_nilai_pertandingan_sudah_selesai()
    {
        $matchId = $this->createDummyMatchWithJuris('finished');

        $response = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'Pertandingan tidak sedang berlangsung'
        ]);
    }

    /**
     * TC-JR-09: Juri melakukan input dengan kategori nilai invalid
     * Expected Result: Sistem mengembalikan pesan: "Parameter tidak valid"
     */
    public function test_tc_jr_09_input_nilai_kategori_invalid()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        $response = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 99
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'Parameter tidak valid'
        ]);
    }

    // ==========================================
    // TC-JR-10 & 11: KONSENSUS ALGORITHM TESTS
    // ==========================================

    /**
     * TC-JR-10: Algoritma Konsensus: Input 2 Juri dalam jeda kurang dari 3 detik (Masuk Poin)
     * Expected Result: Sistem mendeteksi inputCount >= 2, mengeksekusi resolveGroup, nilai sah (menambah skor_merah di skor_pertandingan)
     */
    public function test_tc_jr_10_algoritma_konsensus_2_juri_sah()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        // Juri 1 input Pukulan Merah
        $res1 = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1 // Pukulan = 1 poin
        ]);
        $res1->assertStatus(200);

        // Juri 2 input Pukulan Merah pada pertandingan yang sama
        $res2 = $this->loginAsJuri2()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);
        $res2->assertStatus(200);

        // Cek bahwa skor_merah bertambah di database skor_pertandingan
        $this->assertDatabaseHas('skor_pertandingan', [
            'id_pertandingan' => $matchId,
            'skor_merah' => 1
        ]);

        // Cek score_awards tercatat
        $this->assertDatabaseHas('score_awards', [
            'match_id' => $matchId,
            'athlete' => 'red',
            'technique' => 'punch'
        ]);
    }

    /**
     * TC-JR-11: Algoritma Konsensus: Input hanya 1 Juri melebihi batas waktu 3 detik
     * Expected Result: Nilai TIDAK SAH, status di score_events berubah dari pending menjadi expired
     */
    public function test_tc_jr_11_algoritma_konsensus_1_juri_expired()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        // Juri 1 input Pukulan Biru
        $response = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'biru',
            'id_kategori_nilai' => 1
        ]);
        $response->assertStatus(200);

        $windowId = $response->json()['data']['window_id'];

        // Simulasikan waktu berlalu > 3 detik dengan mengubah opened_at window
        DB::table('score_windows')
            ->where('id', $windowId)
            ->update(['opened_at' => microtime(true) - 10.0]);

        // Panggil resolveExpiredEvents via Usecase
        $juriUsecase = new \App\Http\Usecases\JuriUsecase();
        $juriUsecase->resolveExpiredEvents($matchId);

        // Status window dan score_events menjadi expired
        $this->assertDatabaseHas('score_windows', [
            'id' => $windowId,
            'status' => 'expired'
        ]);
    }

    // ==========================================
    // TC-JR-12 & 13: DELETE SCORE TESTS
    // ==========================================

    /**
     * TC-JR-12: Hapus Nilai (Delete Score): Juri menekan tombol Hapus saat masih pending
     * Expected Result: Status di score_events berubah ke deleted, tercatat di log_activity_juri dengan alasan "Dihapus manual oleh juri"
     */
    public function test_tc_jr_12_hapus_nilai_saat_masih_pending()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        // Juri 1 input Tendangan Merah
        $inputRes = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 2 // Tendangan
        ]);
        $eventId = $inputRes->json()['data']['score_event_id'];

        // Juri 1 hapus nilai (Undo)
        $delRes = $this->loginAsJuri1()->postJson('/juri/delete-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 2
        ]);
        $delRes->assertStatus(200);

        $this->assertDatabaseHas('score_events', [
            'id' => $eventId,
            'status' => 'deleted',
            'deleted_reason' => 'Dihapus manual oleh juri'
        ]);
    }

    /**
     * TC-JR-13: Hapus Nilai (Delete Score): Tombol Hapus ditekan ketika tidak ada nilai pending
     * Expected Result: Sistem mengembalikan pesan "Tidak ada nilai pending untuk dihapus"
     */
    public function test_tc_jr_13_hapus_nilai_tanpa_nilai_pending()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        $response = $this->loginAsJuri1()->postJson('/juri/delete-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'Tidak ada nilai pending untuk dihapus'
        ]);
    }

    // ==========================================
    // TC-JR-14 & 15: HISTORY & RACE CONDITION TESTS
    // ==========================================

    /**
     * TC-JR-14: Fitur Ambil Riwayat (/juri/history)
     * Expected Result: JSON Response memuat history (dengan flag is_sah), juri, dan timer
     */
    public function test_tc_jr_14_fitur_ambil_riwayat()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        // Input 1 nilai oleh Juri 1
        $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);

        $response = $this->loginAsJuri1()->getJson('/juri/history?id_pertandingan=' . $matchId);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'history',
                         'juri',
                         'timer'
                     ]
                 ]);
    }

    /**
     * TC-JR-15: Race Condition Constraint pada Input Score
     * Expected Result: Mekanisme Cache::lock berjalan sukses, kedua request dimasukkan ke dalam window_id yang SAMA
     */
    public function test_tc_jr_15_race_condition_constraint_input_score()
    {
        $matchId = $this->createDummyMatchWithJuris('playing');

        // Juri 1 input Pukulan Merah
        $res1 = $this->loginAsJuri1()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);
        $windowId1 = $res1->json()['data']['window_id'];

        // Juri 2 input Pukulan Merah segera setelahnya
        $res2 = $this->loginAsJuri2()->postJson('/juri/input-score', [
            'id_pertandingan' => $matchId,
            'id_babak' => 1,
            'sudut' => 'merah',
            'id_kategori_nilai' => 1
        ]);
        $windowId2 = $res2->json()['data']['window_id'];

        // Keduanya harus masuk ke window_id yang SAMA
        $this->assertEquals($windowId1, $windowId2);
    }
}
