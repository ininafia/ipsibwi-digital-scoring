<?php

namespace App\Http\Controllers\Dewan;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PetugasPertandinganController extends Controller
{
    public function index(): View | Response | RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/dewan');
        }

        // KHUSUS DEWAN (Role = 3)
        if (session('role') != 3) {
            abort(403, 'Akses ditolak');
        }

        $petugasUsecase = new \App\Http\Usecases\PetugasUsecase();
        $result = $petugasUsecase->getAssignedData();
        $assignedList = $result['data']['list'] ?? [];

        return view('Dewan.Petugas-Pertandingan.list', compact('assignedList'));
    }

    public function add(): View | Response | RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/dewan');
        }

        // KHUSUS DEWAN (Role = 3)
        if (session('role') != 3) {
            abort(403, 'Akses ditolak');
        }

        // FETCH DROPDOWN DATA
        $petugasUsecase = new \App\Http\Usecases\PetugasUsecase();
        $dropdownData = $petugasUsecase->getDropdownData();
        $pertandinganList = $dropdownData['data']['pertandingan'] ?? [];
        $petugasList = $dropdownData['data']['petugas'] ?? [];

        return view('Dewan.Petugas-Pertandingan.add', compact('pertandinganList', 'petugasList'));
    }

    public function store(Request $request): RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/dewan');
        }

        // KHUSUS DEWAN (Role = 3)
        if (session('role') != 3) {
            abort(403, 'Akses ditolak');
        }

        $petugasUsecase = new \App\Http\Usecases\PetugasUsecase();
        $result = $petugasUsecase->assignPetugas($request);

        if (empty($result['success']) || $result['success'] !== true) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result['message'] ?? 'Terjadi kesalahan saat menyimpan penugasan');
        }

        return redirect()
            ->route('dewan.petugas')
            ->with('success', $result['message'] ?? 'Berhasil menugaskan petugas ke pertandingan');
    }
}
