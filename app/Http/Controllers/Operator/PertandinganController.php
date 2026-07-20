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

        // Reset timer cache
        \Illuminate\Support\Facades\Cache::forget('current_timer_state_' . $id);

        event(new \App\Events\MatchUpdated($id));

        // 3. Tampilkan halaman play dengan data pertandingan
        return view('Operator.pertandingan.play', [
            'data' => (object) $result['data'],
        ]);
    }
    public function finalisasi(\Illuminate\Http\Request $request, int $id): RedirectResponse
    {
        // Hanya jenis_kemenangan dari form; pemenang dihitung server-side dari skor DB (Kecuali Disk/WMP dsb)
        $resultData = [
            'jenis_kemenangan'   => $request->input('jenis_kemenangan'),
            'sudut_pemenang'     => $request->input('sudut_pemenang'),
            'catatan_finalisasi' => $request->input('catatan_finalisasi'),
        ];
        
        $result = $this->usecase->finalizeMatch($id, $resultData);

        if (!$this->isSuccess($result)) {
            return redirect()
                ->back()
                ->with('error', $result['message'] ?? 'Gagal melakukan finalisasi');
        }

        event(new \App\Events\MatchUpdated($id));

        return redirect()
            ->route('operator.tanding.index', ['tab' => 'finished'])
            ->with('success', 'Pertandingan berhasil diselesaikan');
    }
}