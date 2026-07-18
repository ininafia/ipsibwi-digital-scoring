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
            $timerState = Cache::get('current_timer_state_' . $match->id, [
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

            // Ambil riwayat hukuman untuk menampilkan per ronde
            $riwayatHukuman = DB::table('riwayat_hukuman')
                ->where('id_pertandingan', $match->id)
                ->get();
            
            $penaltiesPerRound = [];
            for ($r = 1; $r <= 3; $r++) {
                $penaltiesPerRound[$r] = [
                    'jatuhan_biru' => 0,
                    'jatuhan_merah' => 0,
                    'binaan_biru' => 0,
                    'binaan_merah' => 0,
                    'teguran_biru' => 0,
                    'teguran_merah' => 0,
                    'peringatan_biru' => 0,
                    'peringatan_merah' => 0,
                ];
            }

            foreach ($riwayatHukuman as $rh) {
                $r = $rh->id_babak;
                if ($r >= 1 && $r <= 3) {
                    $field = $rh->jenis_hukuman . '_' . $rh->sudut;
                    if (isset($penaltiesPerRound[$r][$field])) {
                        $val = ($rh->action === 'add') ? 1 : -1;
                        $penaltiesPerRound[$r][$field] += $val;
                    }
                }
            }

            // Format text untuk UI Ketua:
            $penaltiesFormatted = [];
            for ($r = 1; $r <= 3; $r++) {
                $p = $penaltiesPerRound[$r];
                
                $hukumanBiru = '';
                $hukumanBiruPoints = 0;
                if ($p['teguran_biru'] >= 1) { $hukumanBiru .= '-1'; $hukumanBiruPoints += 1; }
                if ($p['teguran_biru'] >= 2) { $hukumanBiru .= '-2'; $hukumanBiruPoints += 2; }
                if ($p['peringatan_biru'] >= 1) { $hukumanBiru .= '-5'; $hukumanBiruPoints += 5; }
                if ($p['peringatan_biru'] >= 2) { $hukumanBiru .= '-10'; $hukumanBiruPoints += 10; }

                $hukumanMerah = '';
                $hukumanMerahPoints = 0;
                if ($p['teguran_merah'] >= 1) { $hukumanMerah .= '-1'; $hukumanMerahPoints += 1; }
                if ($p['teguran_merah'] >= 2) { $hukumanMerah .= '-2'; $hukumanMerahPoints += 2; }
                if ($p['peringatan_merah'] >= 1) { $hukumanMerah .= '-5'; $hukumanMerahPoints += 5; }
                if ($p['peringatan_merah'] >= 2) { $hukumanMerah .= '-10'; $hukumanMerahPoints += 10; }

                $jatuhanBiru = '';
                if ($p['jatuhan_biru'] > 0) {
                    $jatuhanBiru = implode('+', array_fill(0, $p['jatuhan_biru'], '3'));
                }
                
                $jatuhanMerah = '';
                if ($p['jatuhan_merah'] > 0) {
                    $jatuhanMerah = implode('+', array_fill(0, $p['jatuhan_merah'], '3'));
                }

                $penaltiesFormatted[$r] = [
                    'jatuhan_biru' => $jatuhanBiru,
                    'jatuhan_merah' => $jatuhanMerah,
                    'jatuhan_biru_points' => $p['jatuhan_biru'] * 3,
                    'jatuhan_merah_points' => $p['jatuhan_merah'] * 3,
                    'binaan_biru' => $p['binaan_biru'] > 0 ? $p['binaan_biru'] : '',
                    'binaan_merah' => $p['binaan_merah'] > 0 ? $p['binaan_merah'] : '',
                    'hukuman_biru' => $hukumanBiru,
                    'hukuman_merah' => $hukumanMerah,
                    'hukuman_biru_points' => $hukumanBiruPoints,
                    'hukuman_merah_points' => $hukumanMerahPoints,
                ];
            }

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

            // ========================================
            // RIWAYAT SCORE EVENTS PER JURI
            // ========================================
            $allEvents = DB::table('score_events')
                ->where('match_id', $match->id)
                ->whereIn('status', ['consumed', 'expired'])
                ->orderBy('server_time', 'asc')
                ->get();

            // Ambil semua vote judge_id untuk menentukan sah/tidak sah
            $allVoteJudgeIds = [];
            if (!empty($awardIds)) {
                $allVoteRows = DB::table('score_award_votes')
                    ->whereIn('award_id', $awardIds)
                    ->get();
                foreach ($allVoteRows as $vr) {
                    $allVoteJudgeIds[$vr->award_id . '_' . $vr->judge_id] = true;
                }
            }

            // Group: juri_position -> round -> athlete -> [events]
            $eventHistory = [];
            foreach (['juri_1', 'juri_2', 'juri_3'] as $posisi) {
                for ($r = 1; $r <= 3; $r++) {
                    $eventHistory[$posisi][$r] = ['blue' => [], 'red' => []];
                }
            }

            foreach ($allEvents as $evt) {
                // Cari posisi juri dari judge_id
                $juriPosisi = null;
                foreach ($juriMap as $posisi => $info) {
                    if ($info['pp_id'] == $evt->judge_id) {
                        $juriPosisi = $posisi;
                        break;
                    }
                }
                if (!$juriPosisi) continue;

                $isSah = false;
                if ($evt->status === 'consumed' && $evt->award_id) {
                    $key = $evt->award_id . '_' . $evt->judge_id;
                    $isSah = isset($allVoteJudgeIds[$key]);
                }

                $eventHistory[$juriPosisi][$evt->round][$evt->athlete][] = [
                    'value' => $evt->score_value,
                    'sah' => $isSah,
                    'technique' => $evt->technique,
                    'award_id' => $evt->award_id,
                ];
            }

            // ========================================
            // RIWAYAT SCORE AWARDS (UNTUK BARIS "SCORE")
            // ========================================
            $awardHistory = [];
            for ($r = 1; $r <= 3; $r++) {
                $awardHistory[$r] = ['blue' => [], 'red' => []];
            }
            foreach ($awards as $awd) {
                $awardHistory[$awd->round][$awd->athlete][] = [
                    'value' => $awd->score_value,
                    'award_id' => (string) $awd->id,
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
            } else {
                $currentRound = $timerState['round'] ?? 1;
                $pemenang = 'Babak ' . $currentRound;
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
                'penalties_formatted' => $penaltiesFormatted,
                'grand_total' => [
                    'blue' => $grandTotalBlue,
                    'red' => $grandTotalRed,
                ],
                'pemenang' => $pemenang,
                'akurasi' => $akurasi->toArray(),
                'event_history' => $eventHistory,
                'award_history' => $awardHistory,
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
