<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function index(): View | Response | RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        // KHUSUS KETUA PERTANDINGAN (Role = 2)
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        return view('Ketua.dashboard-ketua.index');
    }
}
