<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Usecases\MonitorDisplayUsecase;
use Illuminate\Http\Request;

class MonitorDisplayController extends Controller
{
    protected MonitorDisplayUsecase $monitorDisplayUsecase;

    public function __construct(MonitorDisplayUsecase $monitorDisplayUsecase)
    {
        $this->monitorDisplayUsecase = $monitorDisplayUsecase;
    }

    public function index()
    {
        $match = \Illuminate\Support\Facades\DB::table('pertandingan')
            ->where('status', 'playing')->whereNull('deleted_at')->first();
        return view('Operator.monitor-display.scoreboard', compact('match'));
    }

    public function getData(Request $request)
    {
        // Hanya role yang butuh data pertandingan real-time yang diizinkan
        $allowedRoles = [1, 2, 3, 4, 5]; // operator, ketua, dewan, timer, juri
        if (!session('user_id') || !in_array((int) session('role'), $allowedRoles, true)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $result = $this->monitorDisplayUsecase->getMonitorData();

        if (!$result) {
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success' => true,
            'match' => $result['match'],
            'data' => $result['data'],
        ]);
    }
}
