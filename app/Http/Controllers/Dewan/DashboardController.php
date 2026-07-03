<?php

namespace App\Http\Controllers\Dewan;

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
            return redirect('/login/dewan');
        }

        // KHUSUS DEWAN (Role = 3)
        if (session('role') != 3) {
            abort(403, 'Akses ditolak');
        }

        return view('Dewan.dashboard-dewan.index');
    }
}
