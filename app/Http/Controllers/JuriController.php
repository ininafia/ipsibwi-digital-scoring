<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Usecases\JuriUsecase;

class JuriController extends Controller
{
    protected $usecase;

    private const ALLOWED_ROLES = [5];

    public function __construct(JuriUsecase $usecase)
    {
        $this->usecase = $usecase;
    }

    /**
     * Guard untuk endpoint AJAX (input-score, delete-score, history).
     * Mengembalikan JSON 401 kalau belum login/role salah, supaya frontend
     * (fetch + res.json()) tidak diam-diam gagal karena dapat halaman redirect.
     */
    private function requireJuriAjax(): ?JsonResponse
    {
        if (!session('user_id') || !in_array(session('role'), self::ALLOWED_ROLES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return null;
    }

    public function index(Request $request): View|Response|RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/juri');
        }

        $role = session('role');

        // KHUSUS JURI (Role = 5, 6, 7)
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            abort(403, 'Akses ditolak');
        }

        $routeName = $request->route()->getName(); // 'juri1', 'juri2', 'juri3'
        $juriNumber = str_replace('juri', '', $routeName); // '1', '2', '3'
        // Default fallback to 1 if route is something else
        if (!in_array($juriNumber, ['1', '2', '3'])) {
            $juriNumber = '1';
        }
        $posisiTarget = 'juri_' . $juriNumber;

        // Verifikasi bahwa user yang login memang ditugaskan di posisi ini
        $sessionJuriPosition = session('juri_position');
        if ($sessionJuriPosition && $sessionJuriPosition !== $posisiTarget) {
            abort(403, 'Anda login sebagai ' . strtoupper(str_replace('_', ' ', $sessionJuriPosition)) . ', bukan ' . strtoupper(str_replace('_', ' ', $posisiTarget)));
        }

        $match = DB::table('pertandingan')
            ->where('status', 'playing')
            ->whereNull('deleted_at')
            ->first();

        $namaPosisi = 'JURI ' . $juriNumber;
        $namaPetugas = 'MENUNGGU PENUGASAN';
        
        if ($match) {
            $assignment = DB::table('petugas_pertandingan')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('petugas_pertandingan.id_pertandingan', $match->id)
                ->where('petugas_pertandingan.posisi', $posisiTarget)
                ->first(['data_petugas.nama']);
                
            if ($assignment) {
                $namaPetugas = strtoupper($assignment->nama);
            }
        }

        return view('Juri.index', compact('namaPosisi', 'namaPetugas', 'match', 'posisiTarget', 'juriNumber'));
    }

    public function inputScore(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }
        return response()->json($this->usecase->inputScore($request));
    }

    public function deleteScore(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }
        return response()->json($this->usecase->deleteScore($request));
    }

    public function getHistory(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }
        return response()->json($this->usecase->getHistory($request));
    }
}