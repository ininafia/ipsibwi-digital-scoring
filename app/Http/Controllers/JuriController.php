<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Http\Usecases\JuriUsecase;

class JuriController extends Controller
{
    protected $usecase;

    public function __construct(JuriUsecase $usecase)
    {
        $this->usecase = $usecase;
    }

    public function index(): View | Response | RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/juri');
        }

        $role = session('role');

        // KHUSUS JURI (Role = 5, 6, 7)
        if (!in_array($role, [5, 6, 7])) {
            abort(403, 'Akses ditolak');
        }

        $nama = '';
        if ($role == 5) {
            $nama = 'JURI 1';
        } elseif ($role == 6) {
            $nama = 'JURI 2';
        } elseif ($role == 7) {
            $nama = 'JURI 3';
        }

        $match = \Illuminate\Support\Facades\DB::table('pertandingan')
            ->where('status', 'playing')
            ->whereNull('deleted_at')
            ->first();

        return view('Juri.index', compact('nama', 'match'));
    }

    public function inputScore(\Illuminate\Http\Request $request)
    {
        $request->merge(['id_petugas_pertandingan' => session('user_id')]);
        return response()->json($this->usecase->inputScore($request));
    }

    public function deleteScore(\Illuminate\Http\Request $request)
    {
        $request->merge(['id_petugas_pertandingan' => session('user_id')]);
        return response()->json($this->usecase->deleteScore($request));
    }

    public function getHistory(\Illuminate\Http\Request $request)
    {
        $request->merge(['id_petugas_pertandingan' => session('user_id')]);
        return response()->json($this->usecase->getHistory($request));
    }
}
