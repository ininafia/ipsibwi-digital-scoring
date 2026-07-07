<?php

namespace App\Http\Usecases;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Presenter\Response;
use App\Entities\ResponseEntity;
use Exception;

class JuriUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "JuriUsecase";
    }

    /**
     * Input ketukan juri
     */
    public function inputScore(Request $request): array
    {
        $funcName = $this->className . '.inputScore';
        DB::beginTransaction();

        try {
            $this->resolveExpiredEvents();

            $idPertandingan = $request->input('id_pertandingan');
            $idBabak = $request->input('id_babak');
            $idPetugas = $request->input('id_petugas_pertandingan'); // Ini adalah users.id dari session
            $sudut = $request->input('sudut'); // 'merah' atau 'biru'
            $idKategoriNilai = $request->input('id_kategori_nilai');
            $nilai = $request->input('nilai'); // 1 atau 2

            // Pastikan pertandingan sedang berlangsung (status = 'playing')
            $match = DB::table('pertandingan')->where('id', $idPertandingan)->first();
            if (!$match || $match->status !== 'playing') {
                return Response::buildErrorService('Pertandingan tidak sedang berlangsung');
            }

            // Cari kategori nilai untuk mendapatkan delay_max (batas waktu)
            $kategori = DB::table('kategori_nilai')->where('id', $idKategoriNilai)->first();
            if (!$kategori) {
                return Response::buildErrorService('Kategori nilai tidak ditemukan');
            }

            $currentTime = microtime(true);

            // Resolusi users.id -> data_petugas.id
            $petugas = DB::table('data_petugas')->where('id_user', $idPetugas)->first();
            if (!$petugas) {
                return Response::buildErrorService('Data petugas tidak ditemukan');
            }

            // Resolusi data_petugas.id + id_pertandingan -> petugas_pertandingan.id
            $petugasPertandingan = DB::table('petugas_pertandingan')
                ->where('id_petugas', $petugas->id)
                ->where('id_pertandingan', $idPertandingan)
                ->first();

            if (!$petugasPertandingan) {
                return Response::buildErrorService('Penugasan petugas tidak ditemukan untuk pertandingan ini');
            }

            $judgeId = $petugasPertandingan->id;

            // Pemetaan parameter ke database baru
            $athlete = $sudut === 'merah' ? 'red' : 'blue';
            $technique = $idKategoriNilai == 1 ? 'punch' : 'kick';

            // Langkah 1 — Cari grup window (award_id) yang sedang berjalan (pending) untuk match, round, athlete ini
            $activeWindow = DB::table('score_events')
                ->where('match_id', $idPertandingan)
                ->where('round', $idBabak)
                ->where('athlete', $athlete)
                ->where('status', 'pending')
                ->orderBy('server_time', 'asc')
                ->first();

            $targetAwardId = null;

            if ($activeWindow) {
                // Tentukan delayMax dari kategori input pertama
                $firstKategoriId = $activeWindow->technique === 'punch' ? 1 : 2;
                $firstKategori = DB::table('kategori_nilai')->where('id', $firstKategoriId)->first();
                $delayMax = $firstKategori ? (float) $firstKategori->delay_max : 3.00;

                // Jika masih dalam rentang waktu delayMax sejak input pertama
                if (($currentTime - $activeWindow->server_time) <= $delayMax) {
                    $targetAwardId = $activeWindow->award_id;
                }
            }

            if (!$targetAwardId) {
                // Inisiasi grup window baru dengan award_id acak
                $targetAwardId = Str::random(15);
            } else {
                // Cek apakah juri ini sudah menginput di window yang sama
                $alreadyInput = DB::table('score_events')
                    ->where('award_id', $targetAwardId)
                    ->where('judge_id', $judgeId)
                    ->exists();

                if ($alreadyInput) {
                    DB::rollBack();
                    return Response::buildSuccess(['message' => 'Sudah menginput di event ini (terabaikan)']);
                }
            }

            // Simpan input juri ke score_events sebagai pending
            $newEventId = DB::table('score_events')->insertGetId([
                'match_id' => $idPertandingan,
                'round' => $idBabak,
                'athlete' => $athlete,
                'judge_id' => $judgeId,
                'technique' => $technique,
                'score_value' => $nilai,
                'server_time' => $currentTime,
                'status' => 'pending',
                'award_id' => $targetAwardId,
                'created_at' => now(),
            ]);

            DB::commit();

            // Cek jumlah input unik juri dalam grup window ini
            $inputCount = DB::table('score_events')
                ->where('award_id', $targetAwardId)
                ->count();

            if ($inputCount >= 3) {
                // Jika ketiga juri sudah memberikan input, selesaikan grup secara instan
                $this->resolveGroup($targetAwardId);
            }

            return Response::buildSuccess(['event_id' => $targetAwardId], 200, 'Berhasil mencatat nilai');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Menyelesaikan penilaian untuk suatu grup window (Sah / Tidak Sah)
     */
    public function resolveGroup(string $groupId): void
    {
        $funcName = $this->className . '.resolveGroup';
        DB::beginTransaction();

        try {
            // Cek kembali input untuk group ini (harus pending agar tidak diproses ganda)
            $inputs = DB::table('score_events')
                ->where('award_id', $groupId)
                ->where('status', 'pending')
                ->get();

            if ($inputs->isEmpty()) {
                DB::rollBack();
                return; // Mungkin sudah di-resolve
            }

            $firstInput = $inputs->first();
            
            // Hitung jumlah juri untuk masing-masing kategori teknik
            $techCounts = [];
            foreach ($inputs as $input) {
                if (!isset($techCounts[$input->technique])) {
                    $techCounts[$input->technique] = 0;
                }
                $techCounts[$input->technique]++;
            }

            // Cari kategori teknik dengan dukungan >= 2 juri
            $winningTechnique = null;
            foreach ($techCounts as $tech => $count) {
                if ($count >= 2) {
                    $winningTechnique = $tech;
                    break;
                }
            }

            if ($winningTechnique !== null) {
                // KONSENSUS TERCAPAI (Ada >= 2 juri dengan kategori yang sama)
                $winningInput = $inputs->where('technique', $winningTechnique)->first();
                $winningScoreValue = $winningTechnique === 'punch' ? 1 : 2;

                // 1. Simpan ke tabel score_awards
                $awardIdDb = DB::table('score_awards')->insertGetId([
                    'match_id' => $winningInput->match_id,
                    'round' => $winningInput->round,
                    'athlete' => $winningInput->athlete,
                    'technique' => $winningTechnique,
                    'score_value' => $winningScoreValue,
                    'awarded_time' => $winningInput->server_time,
                    'source' => 'automatic',
                    'created_at' => now(),
                ]);

                // 2. Simpan ke tabel score_award_votes untuk juri pendukung yang sah (kategori sama)
                foreach ($inputs as $input) {
                    if ($input->technique === $winningTechnique) {
                        DB::table('score_award_votes')->insert([
                            'award_id' => $awardIdDb,
                            'score_event_id' => $input->id,
                            'judge_id' => $input->judge_id,
                            'created_at' => now(),
                        ]);
                    }
                }

                // 3. Ubah status semua input dalam grup menjadi consumed dan update award_id ke ID database
                DB::table('score_events')
                    ->where('award_id', $groupId)
                    ->update([
                        'status' => 'consumed',
                        'award_id' => (string) $awardIdDb,
                    ]);

                // 4. Tambahkan poin ke tabel skor_pertandingan
                $skorField = $winningInput->athlete === 'red' ? 'skor_merah' : 'skor_biru';
                $skorRecord = DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $winningInput->match_id)
                    ->first();

                if ($skorRecord) {
                    DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $winningInput->match_id)
                        ->update([
                            $skorField => DB::raw($skorField . ' + ' . $winningScoreValue),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('skor_pertandingan')->insert([
                        'id_pertandingan' => $winningInput->match_id,
                        $skorField => $winningScoreValue,
                        'updated_at' => now(),
                    ]);
                }

            } else {
                // KONSENSUS TIDAK TERCAPAI (Semua input dalam grup dinyatakan tidak sah)
                DB::table('score_events')
                    ->where('award_id', $groupId)
                    ->update(['status' => 'expired']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
        }
    }

    /**
     * Memeriksa dan memutuskan event yang sudah melewati batas waktu (expired).
     */
    public function resolveExpiredEvents(): void
    {
        $funcName = $this->className . '.resolveExpiredEvents';
        try {
            $currentTime = microtime(true);
            
            // Ambil semua group window (award_id) yang masih pending
            $pendingGroups = DB::table('score_events')
                ->select('award_id')
                ->selectRaw('MIN(server_time) as start_time')
                ->where('status', 'pending')
                ->whereNotNull('award_id')
                ->groupBy('award_id')
                ->get();

            foreach ($pendingGroups as $group) {
                // Cari input pertama untuk menentukan delayMax
                $firstInput = DB::table('score_events')
                    ->where('award_id', $group->award_id)
                    ->orderBy('server_time', 'asc')
                    ->first();
                
                if ($firstInput) {
                    $kategoriId = $firstInput->technique === 'punch' ? 1 : 2;
                    $kategori = DB::table('kategori_nilai')->where('id', $kategoriId)->first();
                    $delayMax = $kategori ? (float) $kategori->delay_max : 3.00;

                    if (($currentTime - $group->start_time) > $delayMax) {
                        $this->resolveGroup($group->award_id);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
        }
    }

    public function deleteScore(Request $request): array
    {
        $funcName = $this->className . '.deleteScore';
        DB::beginTransaction();

        try {
            $idPertandingan = $request->input('id_pertandingan');
            $idBabak = $request->input('id_babak');
            $idPetugas = $request->input('id_petugas_pertandingan'); // users.id
            $sudut = $request->input('sudut'); // 'merah' atau 'biru'
            $athlete = $sudut === 'merah' ? 'red' : 'blue';

            $petugas = DB::table('data_petugas')->where('id_user', $idPetugas)->first();
            if (!$petugas) {
                return Response::buildErrorService('Petugas tidak ditemukan');
            }

            $petugasPertandingan = DB::table('petugas_pertandingan')
                ->where('id_petugas', $petugas->id)
                ->where('id_pertandingan', $idPertandingan)
                ->first();

            if (!$petugasPertandingan) {
                return Response::buildErrorService('Penugasan tidak ditemukan');
            }

            // Find the most recent pending input in score_events
            $lastInput = DB::table('score_events')
                ->where('match_id', $idPertandingan)
                ->where('round', $idBabak)
                ->where('judge_id', $petugasPertandingan->id)
                ->where('athlete', $athlete)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastInput) {
                DB::table('score_events')->where('id', $lastInput->id)->delete();
                DB::commit();
                return Response::buildSuccess(null, 200, 'Berhasil menghapus nilai pending');
            }

            DB::rollBack();
            return Response::buildErrorService('Tidak ada nilai pending untuk dihapus');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getHistory(Request $request): array
    {
        $funcName = $this->className . '.getHistory';
        try {
            $this->resolveExpiredEvents();
            $idPertandingan = $request->input('id_pertandingan');
            $idPetugas = $request->input('id_petugas_pertandingan'); // users.id

            $petugas = DB::table('data_petugas')->where('id_user', $idPetugas)->first();
            if (!$petugas) {
                return Response::buildSuccess([], 200, 'Berhasil mengambil riwayat (petugas tidak ditemukan)');
            }

            $petugasPertandingan = DB::table('petugas_pertandingan')
                ->where('id_petugas', $petugas->id)
                ->where('id_pertandingan', $idPertandingan)
                ->first();

            if (!$petugasPertandingan) {
                return Response::buildSuccess([], 200, 'Berhasil mengambil riwayat (penugasan tidak ditemukan)');
            }

            $history = DB::table('score_events')
                ->where('match_id', $idPertandingan)
                ->where('judge_id', $petugasPertandingan->id)
                ->where('status', 'consumed')
                ->orderBy('server_time', 'asc')
                ->get()
                ->map(function($item) {
                    // Check if this event got a vote in score_award_votes
                    $hasVote = DB::table('score_award_votes')
                        ->where('score_event_id', $item->id)
                        ->exists();
                    $isSah = $hasVote;

                    return [
                        'id' => $item->id,
                        'id_pertandingan' => $item->match_id,
                        'id_babak' => $item->round,
                        'id_petugas_pertandingan' => $item->judge_id,
                        'sudut' => $item->athlete === 'red' ? 'merah' : 'biru',
                        'nilai' => $item->score_value,
                        'waktu_input' => $item->server_time,
                        'status' => $item->status,
                        'is_sah' => $isSah,
                    ];
                });

            return Response::buildSuccess($history, 200, 'Berhasil mengambil riwayat');
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }
}
