<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    public function index(): View | Response | RedirectResponse
    {
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        return view('Ketua.Monitor-Ketua.index');
    }

    /**
     * Endpoint AJAX utama untuk halaman Monitor Ketua.
     * Mengembalikan data lengkap pertandingan aktif:
     * - Info match (partai, nama atlet, kontingen)
     * - Skor per juri per ronde (dari score_awards + score_award_votes)
     * - Total skor per ronde (sum dari awards)
     * - Penalti/hukuman per ronde (dari skor_pertandingan)
     * - Timer state
     * - Akurasi juri (jika sudah difinalisasi)
     */
    public function data(): JsonResponse
    {
        if (!session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        if (session('role') != 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            // Cari pertandingan aktif (playing) atau terakhir yang selesai
            $match = DB::table('pertandingan')
                ->whereNull('deleted_at')
                ->whereIn('status', ['playing', 'finished', 'final'])
                ->orderByRaw("CASE status WHEN 'playing' THEN 1 WHEN 'finished' THEN 2 WHEN 'final' THEN 3 END")
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$match) {
                return response()->json(['success' => false, 'message' => 'Tidak ada pertandingan aktif']);
            }

            // Timer state
            $timerState = Cache::get('current_timer_state', [
                'round' => 1,
                'time_remaining' => 120,
                'status' => 'stopped'
            ]);

            // Ambil data juri yang ditugaskan di pertandingan ini
            $juris = DB::table('petugas_pertandingan')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('petugas_pertandingan.id_pertandingan', $match->id)
                ->where('petugas_pertandingan.id_role', 5)
                ->select(
                    'petugas_pertandingan.id as pp_id',
                    'petugas_pertandingan.posisi',
                    'data_petugas.nama'
                )
                ->orderBy('petugas_pertandingan.posisi')
                ->get();

            // Buat mapping posisi -> data juri
            $juriMap = [];
            foreach ($juris as $j) {
                $juriMap[$j->posisi] = [
                    'pp_id' => $j->pp_id,
                    'nama' => $j->nama,
                ];
            }

            // Ambil semua score_awards untuk pertandingan ini
            $awards = DB::table('score_awards')
                ->where('match_id', $match->id)
                ->get();

            // Ambil semua votes untuk awards ini
            $awardIds = $awards->pluck('id')->toArray();
            $votes = collect();
            if (!empty($awardIds)) {
                $votes = DB::table('score_award_votes')
                    ->whereIn('award_id', $awardIds)
                    ->get();
            }

            // Hitung skor per juri per ronde per sudut
            // Skor juri = jumlah score_value dari awards yang di-vote oleh juri tersebut
            $juriScores = [];
            foreach (['juri_1', 'juri_2', 'juri_3'] as $posisi) {
                for ($round = 1; $round <= 3; $round++) {
                    $juriScores[$posisi][$round] = ['blue' => 0, 'red' => 0];
                }
            }

            foreach ($awards as $award) {
                // Cari votes untuk award ini
                $awardVotes = $votes->where('award_id', $award->id);
                foreach ($awardVotes as $vote) {
                    // Cari posisi juri berdasarkan judge_id (pp_id)
                    foreach ($juriMap as $posisi => $info) {
                        if ($info['pp_id'] == $vote->judge_id) {
                            $juriScores[$posisi][$award->round][$award->athlete] += $award->score_value;
                            break;
                        }
                    }
                }
            }

            // Hitung total skor per ronde per sudut (dari awards, bukan per juri)
            $roundTotals = [];
            for ($round = 1; $round <= 3; $round++) {
                $roundAwards = $awards->where('round', $round);
                $roundTotals[$round] = [
                    'blue' => $roundAwards->where('athlete', 'blue')->sum('score_value'),
                    'red' => $roundAwards->where('athlete', 'red')->sum('score_value'),
                ];
            }

            // Ambil data penalti dari skor_pertandingan
            $skor = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $match->id)
                ->first();

            $penalties = [
                'jatuhan_biru' => $skor->jatuhan_biru ?? 0,
                'jatuhan_merah' => $skor->jatuhan_merah ?? 0,
                'binaan_biru' => $skor->binaan_biru ?? 0,
                'binaan_merah' => $skor->binaan_merah ?? 0,
                'teguran_biru' => $skor->teguran_biru ?? 0,
                'teguran_merah' => $skor->teguran_merah ?? 0,
                'peringatan_biru' => $skor->peringatan_biru ?? 0,
                'peringatan_merah' => $skor->peringatan_merah ?? 0,
            ];

            // Grand total (semua ronde)
            $grandTotalBlue = $skor->skor_biru ?? 0;
            $grandTotalRed = $skor->skor_merah ?? 0;

            // Akurasi juri (jika sudah tersimpan)
            $akurasi = DB::table('akurasi_juri')
                ->join('petugas_pertandingan', 'akurasi_juri.id_petugas_pertandingan', '=', 'petugas_pertandingan.id')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('akurasi_juri.id_pertandingan', $match->id)
                ->select(
                    'data_petugas.nama as nama_juri',
                    'petugas_pertandingan.posisi',
                    'akurasi_juri.total_input',
                    'akurasi_juri.total_nilai_sah',
                    'akurasi_juri.total_nilai_tidak_sah',
                    'akurasi_juri.persentase_akurasi'
                )
                ->get();

            // Format juri scores untuk JSON
            $juriScoresFormatted = [];
            foreach (['juri_1', 'juri_2', 'juri_3'] as $posisi) {
                $juriScoresFormatted[$posisi] = [
                    'nama' => $juriMap[$posisi]['nama'] ?? '-',
                    'rounds' => $juriScores[$posisi],
                ];
            }

            // Pemenang
            $pemenang = 'Waiting....';
            if (in_array($match->status, ['finished', 'final'])) {
                if ($grandTotalBlue > $grandTotalRed) {
                    $pemenang = $match->sudut_biru ?? 'Sudut Biru';
                } elseif ($grandTotalRed > $grandTotalBlue) {
                    $pemenang = $match->sudut_merah ?? 'Sudut Merah';
                } else {
                    $pemenang = 'DRAW';
                }
            }

            return response()->json([
                'success' => true,
                'match' => [
                    'id' => $match->id,
                    'partai' => $match->partai ?? '-',
                    'status' => $match->status,
                    'sudut_biru' => $match->sudut_biru ?? '-',
                    'kontingen_biru' => $match->kontingen_biru ?? '-',
                    'sudut_merah' => $match->sudut_merah ?? '-',
                    'kontingen_merah' => $match->kontingen_merah ?? '-',
                ],
                'timer' => [
                    'round' => $timerState['round'] ?? 1,
                    'time_remaining' => $timerState['time_remaining'] ?? 120,
                    'status' => $timerState['status'] ?? 'stopped',
                ],
                'juri_scores' => $juriScoresFormatted,
                'round_totals' => $roundTotals,
                'penalties' => $penalties,
                'grand_total' => [
                    'blue' => $grandTotalBlue,
                    'red' => $grandTotalRed,
                ],
                'pemenang' => $pemenang,
                'akurasi' => $akurasi->toArray(),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MonitorController.data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
