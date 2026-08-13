<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoLoggingController extends Controller
{
    public function index(Request $request)
    {
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $search = $request->input('search');

        $query = DB::table('video_juri_logs')
            ->join('pertandingan', 'video_juri_logs.id_pertandingan', '=', 'pertandingan.id')
            ->select(
                'pertandingan.id as match_id',
                'pertandingan.partai',
                'pertandingan.gelanggang',
                'pertandingan.kelas',
                'pertandingan.golongan',
                'pertandingan.sudut_biru',
                'pertandingan.kontingen_biru',
                'pertandingan.sudut_merah',
                'pertandingan.kontingen_merah',
                'pertandingan.status',
                DB::raw('COUNT(video_juri_logs.id) as total_videos'),
                DB::raw('MAX(video_juri_logs.created_at) as last_recorded')
            )
            ->groupBy(
                'pertandingan.id',
                'pertandingan.partai',
                'pertandingan.gelanggang',
                'pertandingan.kelas',
                'pertandingan.golongan',
                'pertandingan.sudut_biru',
                'pertandingan.kontingen_biru',
                'pertandingan.sudut_merah',
                'pertandingan.kontingen_merah',
                'pertandingan.status'
            )
            ->orderBy('last_recorded', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('pertandingan.sudut_biru', 'like', "%{$search}%")
                  ->orWhere('pertandingan.sudut_merah', 'like', "%{$search}%")
                  ->orWhere('pertandingan.kontingen_biru', 'like', "%{$search}%")
                  ->orWhere('pertandingan.kontingen_merah', 'like', "%{$search}%")
                  ->orWhere('pertandingan.partai', 'like', "%{$search}%");
            });
        }

        $matches = $query->get();

        // Also fetch all standalone video logs if any
        $recentVideos = DB::table('video_juri_logs')
            ->leftJoin('pertandingan', 'video_juri_logs.id_pertandingan', '=', 'pertandingan.id')
            ->select(
                'video_juri_logs.*',
                'pertandingan.partai',
                'pertandingan.kelas',
                'pertandingan.sudut_biru',
                'pertandingan.sudut_merah'
            )
            ->orderBy('video_juri_logs.created_at', 'desc')
            ->limit(20)
            ->get();

        return view('Ketua.VideoLogging.index', compact('matches', 'recentVideos', 'search'));
    }

    public function detail($id)
    {
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $match = DB::table('pertandingan')
            ->where('id', $id)
            ->first();

        if (!$match) {
            abort(404, 'Pertandingan tidak ditemukan');
        }

        $videos = DB::table('video_juri_logs')
            ->where('id_pertandingan', $id)
            ->orderBy('posisi_juri', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group videos by posisi_juri (juri_1, juri_2, juri_3)
        $videosByJuri = [
            'juri_1' => [],
            'juri_2' => [],
            'juri_3' => [],
        ];

        foreach ($videos as $v) {
            $pos = $v->posisi_juri ?? 'juri_1';
            if (!isset($videosByJuri[$pos])) {
                $videosByJuri[$pos] = [];
            }
            $videosByJuri[$pos][] = $v;
        }

        return view('Ketua.VideoLogging.detail', compact('match', 'videos', 'videosByJuri'));
    }
}
