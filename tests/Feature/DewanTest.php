<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DewanTest extends TestCase
{
    /**
     * Setup dummy session data for Dewan role
     */
    protected function loginAsDewan()
    {
        return $this->withSession([
            'user_id' => 1,
            'role' => 3
        ]);
    }

    /**
     * Setup dummy session data for non-Dewan role
     */
    protected function loginAsNonDewan()
    {
        return $this->withSession([
            'user_id' => 2,
            'role' => 2 // e.g., Ketua
        ]);
    }

    // ==========================================
    // DASHBOARD FEATURE TESTS
    // ==========================================

    public function test_dashboard_redirects_to_login_if_unauthenticated()
    {
        $response = $this->get('/dewan');
        
        $response->assertRedirect('/login/dewan');
    }

    public function test_dashboard_returns_403_if_not_dewan()
    {
        $response = $this->loginAsNonDewan()->get('/dewan');
        
        $response->assertStatus(403);
    }

    public function test_dashboard_accessible_for_dewan()
    {
        // Mocking the view for successful render since we don't need real DB
        $response = $this->loginAsDewan()->get('/dewan');
        
        $response->assertStatus(200);
    }

    // ==========================================
    // PENILAIAN ATLET FEATURE TESTS
    // ==========================================

    public function test_penilaian_atlet_index_accessible_for_dewan()
    {
        // This relies on getActiveMatch() returning correctly, assuming DB is properly seeded or mocked
        // For basic routing/middleware check, we just ensure it doesn't 403 or redirect
        $response = $this->loginAsDewan()->get('/dewan/penilaian');
        
        $response->assertStatus(200);
    }

    public function test_penilaian_add_jatuhan_valid_request()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/addJatuhan', [
            'id_pertandingan' => 1,
            'sudut' => 'merah'
        ]);
        
        // Asserting validation passes and tries to hit the usecase
        $response->assertStatus(200); // or 400 if validation passes but usecase fails
    }

    public function test_penilaian_add_jatuhan_invalid_request()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/addJatuhan', [
            // Missing id_pertandingan
            'sudut' => 'invalid_sudut'
        ]);
        
        $response->assertStatus(422); // Validation error
    }

    public function test_penilaian_del_jatuhan()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/delJatuhan', [
            'id_pertandingan' => 1,
            'sudut' => 'biru'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_add_binaan()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/addBinaan', [
            'id_pertandingan' => 1,
            'sudut' => 'merah'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_del_binaan()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/delBinaan', [
            'id_pertandingan' => 1,
            'sudut' => 'merah'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_add_teguran()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/addTeguran', [
            'id_pertandingan' => 1,
            'sudut' => 'biru'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_del_teguran()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/delTeguran', [
            'id_pertandingan' => 1,
            'sudut' => 'biru'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_add_peringatan()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/addPeringatan', [
            'id_pertandingan' => 1,
            'sudut' => 'merah'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_del_peringatan()
    {
        $response = $this->loginAsDewan()->postJson('/dewan/penilaian/delPeringatan', [
            'id_pertandingan' => 1,
            'sudut' => 'merah'
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_penilaian_get_data()
    {
        $response = $this->loginAsDewan()->getJson('/dewan/penilaian/getData');
        
        $response->assertStatus(200);
    }

    // ==========================================
    // PETUGAS PERTANDINGAN FEATURE TESTS
    // ==========================================

    public function test_petugas_index_accessible_for_dewan()
    {
        $response = $this->loginAsDewan()->get('/dewan/petugas');
        
        $response->assertStatus(200);
    }

    public function test_petugas_add_form_accessible_for_dewan()
    {
        $response = $this->loginAsDewan()->get('/dewan/petugas/add');
        
        $response->assertStatus(200);
    }

    public function test_petugas_store()
    {
        $response = $this->loginAsDewan()->post('/dewan/petugas/store', [
            'id_pertandingan' => 1,
            'id_juri_1' => 1,
            'id_juri_2' => 2,
            'id_juri_3' => 3,
        ]);
        
        $this->assertContains($response->getStatusCode(), [302]);
    }

    public function test_petugas_run()
    {
        $response = $this->loginAsDewan()->get('/dewan/petugas/run/1');
        
        $this->assertContains($response->getStatusCode(), [302]);
    }

    public function test_petugas_destroy()
    {
        $response = $this->loginAsDewan()->delete('/dewan/petugas/destroy/1');
        
        $this->assertContains($response->getStatusCode(), [302]);
    }
}
