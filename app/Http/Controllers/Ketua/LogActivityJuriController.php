<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogActivityJuriController extends Controller
{
    public function index()
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        // KHUSUS KETUA (Role = 2)
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $logs = DB::table('log_activity_juri')
            ->join('pertandingan', 'log_activity_juri.id_pertandingan', '=', 'pertandingan.id')
            ->join('petugas_pertandingan', 'log_activity_juri.id_juri', '=', 'petugas_pertandingan.id')
            ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
            ->leftJoin('score_events', 'log_activity_juri.id_score_event', '=', 'score_events.id')
            ->leftJoin('score_award_votes', 'score_events.id', '=', 'score_award_votes.score_event_id')
            ->select(
                'log_activity_juri.*',
                'pertandingan.partai',
                'pertandingan.kelas',
                'pertandingan.golongan',
                'pertandingan.gelanggang',
                'data_petugas.nama as nama_juri',
                'petugas_pertandingan.posisi',
                'score_events.status as event_status',
                'score_events.technique as technique',
                'score_events.athlete as athlete',
                'score_award_votes.id as is_sah'
            )
            ->orderBy('log_activity_juri.created_at', 'asc')
            ->get();

        // Group by match and then cluster by time
        $groupedLogs = [];
        foreach ($logs as $log) {
            $matchKey = $log->id_pertandingan;
            if (!isset($groupedLogs[$matchKey])) {
                $groupedLogs[$matchKey] = [
                    'match_info' => [
                        'partai' => $log->partai,
                        'kelas' => $log->kelas,
                        'golongan' => $log->golongan,
                        'gelanggang' => $log->gelanggang
                    ],
                    'clusters' => []
                ];
            }
            
            $log->status_text = '-';
            if ($log->id_score_event) {
                // Filter: Hanya tampilkan yang Sah atau Tidak Sah (Abaikan Menunggu/Pending/Deleted)
                if ($log->event_status === 'pending' || $log->event_status === 'deleted') {
                    continue;
                }

                if ($log->event_status === 'expired') {
                    $log->status_text = 'Tidak Sah';
                } elseif ($log->event_status === 'consumed') {
                    if ($log->is_sah) {
                        $log->status_text = 'Sah';
                    } else {
                        $log->status_text = 'Tidak Sah';
                    }
                }
            } else {
                continue;
            }

            // Cluster logic: if within 3 seconds of the last cluster, add to it.
            $logTime = \Carbon\Carbon::parse($log->created_at);
            $clusters = &$groupedLogs[$matchKey]['clusters'];
            
            $added = false;
            if (count($clusters) > 0) {
                $lastCluster = &$clusters[count($clusters) - 1];
                $lastTime = \Carbon\Carbon::parse($lastCluster['time']);
                if ($logTime->diffInSeconds($lastTime) <= 3) {
                    // Same cluster
                    $pos = $log->posisi; // 'juri_1', 'juri_2', 'juri_3'
                    if (!isset($lastCluster['events'][$pos])) {
                        $lastCluster['events'][$pos] = $log;
                        $added = true;
                    }
                }
            }

            if (!$added) {
                $pos = $log->posisi;
                $clusters[] = [
                    'time' => $log->created_at, // Use the time of the first event as axis point
                    'events' => [
                        $pos => $log
                    ]
                ];
            }
        }

        // Hapus match yang tidak memiliki cluster (karena di-filter)
        foreach ($groupedLogs as $key => $group) {
            if (count($group['clusters']) == 0) {
                unset($groupedLogs[$key]);
            }
        }

        // Sort grouped logs by partai sequentially
        uasort($groupedLogs, function($a, $b) {
            return (int)$a['match_info']['partai'] <=> (int)$b['match_info']['partai'];
        });

        return view('Ketua.Log-juri.index', compact('groupedLogs'));
    }
}
