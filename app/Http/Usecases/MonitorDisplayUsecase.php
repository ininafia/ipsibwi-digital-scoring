<?php

namespace App\Http\Usecases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MonitorDisplayUsecase extends Usecase
{
    public string $className = "MonitorDisplayUsecase";

    public function getMonitorData()
    {
        $match = DB::table('pertandingan')
            ->where('status', 'playing')
            ->whereNull('deleted_at')
            ->first();
            
        if (!$match) return null;
        
        $score = DB::table('skor_pertandingan')
            ->where('id_pertandingan', $match->id)
            ->first();
            
        // Fetch Timer State from Cache
        $timerState = Cache::get('current_timer_state_' . $match->id, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);

        // Fetch Pending Votes
        $pendingVotes = DB::table('score_events')
            ->join('petugas_pertandingan', 'score_events.judge_id', '=', 'petugas_pertandingan.id')
            ->where('score_events.match_id', $match->id)
            ->where('score_events.status', 'pending')
            ->select('score_events.athlete', 'score_events.technique', 'petugas_pertandingan.posisi')
            ->get();
            
        $activeVotes = [
            'blue' => ['punch' => [], 'kick' => []],
            'red'  => ['punch' => [], 'kick' => []]
        ];

        foreach ($pendingVotes as $vote) {
            $juriIndex = str_replace('juri_', 'J', $vote->posisi); // e.g. 'juri_1' -> 'J1'
            $activeVotes[$vote->athlete][$vote->technique][] = $juriIndex;
        }

        return [
            'match' => [
                'id' => $match->id,
                'partai' => $match->partai ?? '-',
                'sudut_biru' => $match->sudut_biru ?? '-',
                'kontingen_biru' => $match->kontingen_biru ?? '-',
                'sudut_merah' => $match->sudut_merah ?? '-',
                'kontingen_merah' => $match->kontingen_merah ?? '-',
                'round' => $timerState['round'] ?? 1,
                'time_remaining' => $timerState['time_remaining'] ?? 120,
                'timer_status' => $timerState['status'] ?? 'stopped'
            ],
            'data' => [
                'skor_biru' => $score->skor_biru ?? 0,
                'skor_merah' => $score->skor_merah ?? 0,
                'binaan_biru' => $score->binaan_biru ?? 0,
                'binaan_merah' => $score->binaan_merah ?? 0,
                'teguran_biru' => $score->teguran_biru ?? 0,
                'teguran_merah' => $score->teguran_merah ?? 0,
                'peringatan_biru' => $score->peringatan_biru ?? 0,
                'peringatan_merah' => $score->peringatan_merah ?? 0,
                'jatuhan_biru' => $score->jatuhan_biru ?? 0,
                'jatuhan_merah' => $score->jatuhan_merah ?? 0,
            ],
            'active_votes' => $activeVotes
        ];
    }
}
