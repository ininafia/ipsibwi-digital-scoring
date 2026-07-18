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
                ->first();
            if (!$match) return response()->json(['error' => 'No active match'], 404);
            $matchId = $match->id;
        }

        $data = $request->only(['round', 'time_remaining', 'status']);
        $result = $this->usecase->syncState($matchId, $data);
        
        if (isset($result['success']) && !$result['success']) {
            return response()->json($result, $result['code'] ?? 400);
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
