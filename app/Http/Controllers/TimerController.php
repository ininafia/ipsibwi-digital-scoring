<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class TimerController extends Controller
{
    protected $usecase;

    public function __construct(\App\Http\Usecases\TimerUsecase $usecase)
    {
        $this->usecase = $usecase;
    }

    public function index(): View | Response | RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/timer');
        }

        // KHUSUS TIMER (Role = 4)
        if (session('role') != 4) {
            abort(403, 'Akses ditolak');
        }

        $match = \Illuminate\Support\Facades\DB::table('pertandingan')
            ->where('status', 'playing')
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->first();

        return view('Timer.index', compact('match'));
    }

    public function sync(\Illuminate\Http\Request $request)
    {
        if (session('role') != 4) return response()->json(['error' => 'Unauthorized'], 403);
        
        $matchId = $request->input('id_pertandingan');
        if (!$matchId) {
            $match = \Illuminate\Support\Facades\DB::table('pertandingan')
                ->where('status', 'playing')
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'desc')
                ->first();
            if (!$match) return response()->json(['error' => 'No active match'], 404);
            $matchId = $match->id;
        } else {
            // BUG-6 FIX: Validasi bahwa id_pertandingan yang dikirim timer
            // memang statusnya 'playing' agar timer tidak manipulasi pertandingan lain
            $match = \Illuminate\Support\Facades\DB::table('pertandingan')
                ->where('id', $matchId)
                ->where('status', 'playing')
                ->whereNull('deleted_at')
                ->first();
            if (!$match) {
                return response()->json(['error' => 'Pertandingan tidak ditemukan atau tidak sedang berjalan'], 403);
            }
        }

        $data = $request->only(['round', 'time_remaining', 'status']);
        $result = $this->usecase->syncState($matchId, $data);
        
        if (isset($result['success']) && !$result['success']) {
            return response()->json($result, $result['code'] ?? 400);
        }
        
        if (!empty($result['should_broadcast'])) {
            event(new \App\Events\MatchUpdated($matchId));
        }
        
        return response()->json($result);
    }

    public function getState(\Illuminate\Http\Request $request)
    {
        $matchId = $request->input('id_pertandingan');
        if (!$matchId) {
            $match = \Illuminate\Support\Facades\DB::table('pertandingan')
                ->where('status', 'playing')
                ->whereNull('deleted_at')
                ->first();
            if (!$match) return response()->json(['error' => 'No active match'], 404);
            $matchId = $match->id;
        }

        return response()->json($this->usecase->getState($matchId));
    }
}
