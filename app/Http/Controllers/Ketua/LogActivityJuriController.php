<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogActivityJuriController extends Controller
{
    public function index(Request $request)
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        // KHUSUS KETUA (Role = 2)
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $partaiFilter = $request->input('partai');
        $babakFilter = $request->input('babak');

        $availableMatches = DB::table('pertandingan')->whereNull('deleted_at')->orderBy('partai')->get(['id', 'partai', 'gelanggang']);
        $availableRounds = DB::table('babak')->orderBy('babak_ke')->get(['id', 'babak_ke']);

        $query = DB::table('log_activity_juri')
            ->join('pertandingan', 'log_activity_juri.id_pertandingan', '=', 'pertandingan.id')
            ->join('petugas_pertandingan', 'log_activity_juri.id_juri', '=', 'petugas_pertandingan.id')
            ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
            ->join('babak', 'log_activity_juri.id_babak', '=', 'babak.id')
            ->leftJoin('score_events', 'log_activity_juri.id_score_event', '=', 'score_events.id')
            ->leftJoin('score_award_votes', 'score_events.id', '=', 'score_award_votes.score_event_id')
            ->whereNull('pertandingan.deleted_at')
            ->select(
                'log_activity_juri.*',
                'pertandingan.partai',
                'pertandingan.kelas',
                'pertandingan.golongan',
                'pertandingan.gelanggang',
                'babak.babak_ke',
                'data_petugas.nama as nama_juri',
                'petugas_pertandingan.posisi',
                'score_events.status as event_status',
                'score_events.technique as technique',
                'score_events.athlete as athlete',
                'score_award_votes.id as is_sah'
            )
            ->orderBy('log_activity_juri.created_at', 'asc');

        if ($partaiFilter) {
            $query->where('pertandingan.id', $partaiFilter);
        }
        if ($babakFilter) {
            $query->where('log_activity_juri.id_babak', $babakFilter);
        }

        $logs = $query->get();

        // Group by match AND babak
        $groupedLogsBabak = [];

        foreach ($logs as $log) {
            $log->status_text = '-';
            if ($log->id_score_event) {
                if ($log->event_status === 'pending' || $log->event_status === 'deleted') {
                    continue;
                }
                if ($log->event_status === 'expired') {
                    $log->status_text = 'Tidak Sah';
                } elseif ($log->event_status === 'consumed') {
                    $log->status_text = $log->is_sah ? 'Sah' : 'Tidak Sah';
                }
            } else {
                continue;
            }

            // Group by Babak (Nested under Partai)
            $matchKeyPartai = $log->id_pertandingan;
            if (!isset($groupedLogsBabak[$matchKeyPartai])) {
                $groupedLogsBabak[$matchKeyPartai] = [
                    'match_info' => [
                        'partai' => $log->partai,
                        'kelas' => $log->kelas,
                        'golongan' => $log->golongan,
                        'gelanggang' => $log->gelanggang
                    ],
                    'babak' => []
                ];
            }
            $babakKey = $log->id_babak;
            if (!isset($groupedLogsBabak[$matchKeyPartai]['babak'][$babakKey])) {
                $groupedLogsBabak[$matchKeyPartai]['babak'][$babakKey] = [
                    'babak_ke' => $log->babak_ke,
                    'clusters' => []
                ];
            }
            $this->addLogToClusters($groupedLogsBabak[$matchKeyPartai]['babak'][$babakKey]['clusters'], clone $log);
        }

        // Hapus match yang tidak memiliki cluster
        foreach ($groupedLogsBabak as $key => $group) {
            foreach ($group['babak'] as $bKey => $bData) {
                if (count($bData['clusters']) == 0) {
                    unset($groupedLogsBabak[$key]['babak'][$bKey]);
                }
            }
            if (count($groupedLogsBabak[$key]['babak']) == 0) {
                unset($groupedLogsBabak[$key]);
            }
        }

        // Sort grouped logs
        uasort($groupedLogsBabak, function($a, $b) {
            return (int)$a['match_info']['partai'] <=> (int)$b['match_info']['partai'];
        });

        return view('Ketua.Log-juri.index', compact('groupedLogsBabak', 'availableMatches', 'availableRounds', 'partaiFilter', 'babakFilter'));
    }

    private function addLogToClusters(&$clusters, $log)
    {
        $logTime = \Carbon\Carbon::parse($log->created_at);
        $added = false;
        
        if (count($clusters) > 0) {
            $lastCluster = &$clusters[count($clusters) - 1];
            $lastTime = \Carbon\Carbon::parse($lastCluster['time']);
            if ($logTime->diffInSeconds($lastTime) <= 3) {
                $pos = $log->posisi;
                if (!isset($lastCluster['events'][$pos])) {
                    $lastCluster['events'][$pos] = $log;
                    $added = true;
                }
            }
        }

        if (!$added) {
            $pos = $log->posisi;
            $clusters[] = [
                'time' => $log->created_at,
                'events' => [
                    $pos => $log
                ]
            ];
        }
    }
}
