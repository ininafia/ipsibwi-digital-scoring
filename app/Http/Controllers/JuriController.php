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

    public function index(): View|Response|RedirectResponse
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

        $match = DB::table('pertandingan')
            ->where('status', 'playing')
            ->whereNull('deleted_at')
            ->first();

        $namaPosisi = 'JURI';
        $namaPetugas = 'JURI';
        if ($match) {
            $petugas = DB::table('data_petugas')
                ->where('id_user', session('user_id'))
                ->first();
            
            if ($petugas) {
                $namaPetugas = strtoupper($petugas->nama);
                $posisi = DB::table('petugas_pertandingan')
                    ->where('id_pertandingan', $match->id)
                    ->where('id_petugas', $petugas->id)
                    ->value('posisi');
                
                if ($posisi) {
                    $namaPosisi = strtoupper(str_replace('_', ' ', $posisi));
                }
            }
        }

        return view('Juri.index', compact('namaPosisi', 'namaPetugas', 'match'));
    }

    public function inputScore(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }

        $request->merge(['id_petugas_pertandingan' => session('user_id')]);
        return response()->json($this->usecase->inputScore($request));
    }

    public function deleteScore(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }

        $request->merge(['id_petugas_pertandingan' => session('user_id')]);
        return response()->json($this->usecase->deleteScore($request));
    }

    public function getHistory(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }

        $request->merge(['id_petugas_pertandingan' => session('user_id')]);
        return response()->json($this->usecase->getHistory($request));
    }
}