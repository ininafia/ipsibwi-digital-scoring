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
            ->select(
                'log_activity_juri.*',
                'pertandingan.partai',
                'pertandingan.kelas',
                'pertandingan.golongan',
                'pertandingan.gelanggang',
                'data_petugas.nama as nama_juri',
                'petugas_pertandingan.posisi'
            )
            ->orderBy('log_activity_juri.created_at', 'desc')
            ->get();

        // Group by match for easier view
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
                    'logs' => []
                ];
            }
            $groupedLogs[$matchKey]['logs'][] = $log;
        }

        return view('Ketua.Log-juri.index', compact('groupedLogs'));
    }
}
