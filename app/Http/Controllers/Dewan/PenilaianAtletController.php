<?php

namespace App\Http\Controllers\Dewan;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Http\Usecases\PertandinganUsecase;

class PenilaianAtletController extends Controller
{
    protected $pertandinganUsecase;

    public function __construct(PertandinganUsecase $pertandinganUsecase)
    {
        $this->pertandinganUsecase = $pertandinganUsecase;
    }

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

        // Ambil data pertandingan yang sedang berlangsung
        $res = $this->pertandinganUsecase->getActiveMatch();
        $pertandingan = $res['success'] ? (object)$res['data'] : null;

        return view('Dewan.penilaian-atlet.index', compact('pertandingan'));
    }

    public function addJatuhan(\Illuminate\Http\Request $request, \App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        $request->validate([
            'id_pertandingan' => 'required|integer',
            'sudut' => 'required|in:biru,merah'
        ]);

        $res = $penilaianAtletUsecase->addJatuhan($request->id_pertandingan, $request->sudut);
        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json(['success' => true, 'message' => $res['message']]);
    }

    public function delJatuhan(\Illuminate\Http\Request $request, \App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        $request->validate([
            'id_pertandingan' => 'required|integer',
            'sudut' => 'required|in:biru,merah'
        ]);

        $res = $penilaianAtletUsecase->delJatuhan($request->id_pertandingan, $request->sudut);
        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json(['success' => true, 'message' => $res['message']]);
    }

    public function addBinaan(\Illuminate\Http\Request $request, \App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        $request->validate([
            'id_pertandingan' => 'required|integer',
            'sudut' => 'required|in:biru,merah'
        ]);

        $res = $penilaianAtletUsecase->addBinaan($request->id_pertandingan, $request->sudut);
        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json(['success' => true, 'message' => $res['message']]);
    }

    public function addTeguran(\Illuminate\Http\Request $request, \App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        $request->validate([
            'id_pertandingan' => 'required|integer',
            'sudut' => 'required|in:biru,merah'
        ]);

        $res = $penilaianAtletUsecase->addTeguran($request->id_pertandingan, $request->sudut);
        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json(['success' => true, 'message' => $res['message']]);
    }

    public function addPeringatan(\Illuminate\Http\Request $request, \App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        $request->validate([
            'id_pertandingan' => 'required|integer',
            'sudut' => 'required|in:biru,merah'
        ]);

        $res = $penilaianAtletUsecase->addPeringatan($request->id_pertandingan, $request->sudut);
        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json(['success' => true, 'message' => $res['message']]);
    }

    public function delHukuman(\Illuminate\Http\Request $request, \App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        $request->validate([
            'id_pertandingan' => 'required|integer',
            'sudut' => 'required|in:biru,merah'
        ]);

        $res = $penilaianAtletUsecase->delHukuman($request->id_pertandingan, $request->sudut);
        if (!$res['success']) {
            return response()->json(['success' => false, 'message' => $res['message']], 400);
        }

        return response()->json(['success' => true, 'message' => $res['message']]);
    }

    public function getData(\App\Http\Usecases\PenilaianAtletUsecase $penilaianAtletUsecase)
    {
        return response()->json($penilaianAtletUsecase->getData());
    }
}
