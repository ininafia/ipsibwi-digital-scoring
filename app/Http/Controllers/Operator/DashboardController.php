<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function __construct() {}

    public function index(): View | Response | RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login');
        }

        // KHUSUS OPERATOR
        if (session('role') != 1) {
            abort(403, 'Akses ditolak');
        }

        return view('Operator.dashboard.index');
    }
}