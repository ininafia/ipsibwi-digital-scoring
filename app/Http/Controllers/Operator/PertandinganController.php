<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Usecases\PertandinganUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class PertandinganController extends Controller
{
    protected string $className;
    protected PertandinganUsecase $usecase;

    public function __construct()
    {
        $this->className = self::class;
        $this->usecase   = new PertandinganUsecase();
    }

    /**
     * =========================
     * AUTH CHECK
     * =========================
     */
    private function authCheck(): ?RedirectResponse
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        if (session('role') != 1) {
            abort(403, 'Akses ditolak');
        }

        return null;
    }

    /**
     * =========================
     * CHECK RESPONSE STATUS
     * =========================
     */
    private function isSuccess(array|null $result): bool
    {
        return is_array($result)
            && isset($result['success'])
            && $result['success'] === true;
    }

    /**
     * =========================
     * PLAY PERTANDINGAN
     * Ambil data pertandingan berdasarkan ID,
     * lalu ubah status menjadi 'playing' dan
     * tampilkan halaman play.
     * =========================
     */
    public function play(int $id): View|Response|RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        // Cek apakah ada pertandingan yang sedang berjalan
        $activeMatch = $this->usecase->getActiveMatch();
        if ($this->isSuccess($activeMatch) && $activeMatch['data']['id'] != $id) {
            return redirect()
                ->route('operator.tanding.index')
                ->with('error', 'Tidak dapat memulai pertandingan. Masih ada pertandingan yang sedang berlangsung (Partai ' . $activeMatch['data']['partai'] . ').');
        }

        // 1. Ambil data pertandingan
        $result = $this->usecase->getByID($id);

        if (!$this->isSuccess($result)) {
            return redirect()
                ->route('operator.tanding.index')
                ->with(
                    'error',
                    $result['message'] ?? 'Data pertandingan tidak ditemukan'
                );
        }

        // 2. Ubah status menjadi 'playing'
        $this->usecase->updateStatus($id, 'playing');

        // 3. Tampilkan halaman play dengan data pertandingan
        return view('Operator.pertandingan.play', [
            'data' => (object) $result['data'],
        ]);
    }
    public function finalisasi(\Illuminate\Http\Request $request, int $id): RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        // Anda bisa memproses $request->sudut_pemenang dan $request->jenis_kemenangan di sini 
        // jika sudah ada tabel/kolom yang disiapkan untuk menyimpannya.
        
        $result = $this->usecase->updateStatus($id, 'finished');

        if (!$this->isSuccess($result)) {
            return redirect()
                ->back()
                ->with('error', $result['message'] ?? 'Gagal melakukan finalisasi');
        }

        return redirect()
            ->route('operator.tanding.index', ['tab' => 'finished'])
            ->with('success', 'Pertandingan berhasil diselesaikan');
    }
}