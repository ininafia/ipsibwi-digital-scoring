<?php

namespace App\Http\Usecases;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Presenter\Response;
use Exception;

class JuriUsecase extends Usecase
{
    public string $className;

    // Pemetaan id_kategori_nilai -> teknik. Sumber kebenaran tunggal,
    // dipakai di semua tempat supaya tidak ada angka "1"/"2" tersebar (magic number).
    private const TECHNIQUE_MAP = [
        1 => 'punch', // pukulan
        2 => 'kick',  // tendangan
    ];

    // Nilai skor SELALU ditentukan dari teknik, tidak pernah dari input client langsung.
    private const SCORE_VALUE_MAP = [
        'punch' => 1,
        'kick'  => 2,
    ];

    private const DEFAULT_DELAY = 3.00;

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

        $idPertandingan  = (int) $request->input('id_pertandingan');

        // Bersihkan window kadaluarsa DI LUAR transaksi utama (hindari nested transaction).
        $this->resolveExpiredEvents($idPertandingan);

        $idBabak         = (int) $request->input('id_babak');
        $juriPosition    = session('juri_position'); // Menggunakan session
        $sudut           = $request->input('sudut');         // 'merah' atau 'biru'
        $idKategoriNilai = (int) $request->input('id_kategori_nilai');

        if (!$idPertandingan || !$idBabak || !$juriPosition
            || !in_array($sudut, ['merah', 'biru'], true)
            || !isset(self::TECHNIQUE_MAP[$idKategoriNilai])) {
            return Response::buildErrorService('Parameter tidak valid');
        }

        $athlete    = $sudut === 'merah' ? 'red' : 'blue';
        $technique  = self::TECHNIQUE_MAP[$idKategoriNilai];
        $scoreValue = self::SCORE_VALUE_MAP[$technique]; // bukan dari client

        try {
            // ----------------------------------------------------------------
            // PERBAIKAN RACE CONDITION:
            // Gunakan Cache::lock berbasis (match, round, athlete, technique)
            // agar dua request juri yang datang bersamaan tidak membuat dua
            // window berbeda untuk aksi yang sama.
            // ----------------------------------------------------------------
            $lockKey  = "score_window:{$idPertandingan}:{$idBabak}:{$athlete}:{$technique}";
            $lock     = \Illuminate\Support\Facades\Cache::lock($lockKey, 5); // 5 detik TTL

            if (!$lock->get()) {
                // Request lain sedang memproses window yang sama; tolak agar tidak duplikat.
                return Response::buildErrorService('Input sedang diproses, coba lagi sesaat.');
            }

            try {
                return DB::transaction(function () use (
                    $idPertandingan, $idBabak, $juriPosition, $athlete, $technique, $scoreValue, $idKategoriNilai, $sudut
                ) {
                    // Jangan me-lock table pertandingan karena akan memblokir query juri lain yang masuk bersamaan
                    $match = DB::table('pertandingan')->where('id', $idPertandingan)->first();
                    if (!$match || $match->status !== 'playing') {
                        return Response::buildErrorService('Pertandingan tidak sedang berlangsung');
                    }

                    // Cek status timer, hanya boleh input saat 'playing'
                    $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $idPertandingan, ['status' => 'stopped']);
                    if ($timerState['status'] !== 'playing') {
                        return Response::buildErrorService('Waktu pertandingan sedang berhenti (Timer pause/stop)');
                    }

                    $kategori = DB::table('kategori_nilai')->where('id', $idKategoriNilai)->first();
                    if (!$kategori) {
                        return Response::buildErrorService('Kategori nilai tidak ditemukan');
                    }

                    $petugasPertandingan = DB::table('petugas_pertandingan')
                        ->where('posisi', $juriPosition)
                        ->where('id_pertandingan', $idPertandingan)
                        ->first();

                    if (!$petugasPertandingan) {
                        return Response::buildErrorService('Penugasan petugas tidak ditemukan untuk posisi ' . strtoupper(str_replace('_', ' ', $juriPosition)));
                    }

                    $judgeId     = $petugasPertandingan->id;
                    $currentTime = microtime(true);
                    $delayMax    = $kategori ? (float) $kategori->delay_max : self::DEFAULT_DELAY;

                    // ----------------------------------------------------------------
                    // CARI WINDOW AKTIF di tabel score_windows
                    // Kriteria: pertandingan sama, babak sama, teknik sama, status 'open',
                    // dan masih dalam rentang waktu delay_max.
                    // ----------------------------------------------------------------
                    $targetWindowId = null;

                    $activeWindows = DB::table('score_windows')
                        ->where('match_id', $idPertandingan)
                        ->where('round_id', $idBabak)
                        ->where('technique', $technique)
                        ->where('status', 'open')
                        ->where('opened_at', '>=', $currentTime - $delayMax)
                        ->orderBy('opened_at', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($activeWindows as $window) {
                        // Cek apakah juri ini SUDAH input di window ini
                        $alreadyInput = DB::table('score_events')
                            ->where('window_id', $window->id)
                            ->where('judge_id', $judgeId)
                            ->where('status', 'pending')
                            ->exists();

                        if (!$alreadyInput) {
                            // Window ini valid dan juri belum pernah input di sini
                            $targetWindowId = $window->id;
                            break;
                        }
                        // Jika juri sudah input di window ini, lanjut cari window berikutnya.
                        // Jika tidak ada window lain, $targetWindowId tetap null dan window baru dibuat.
                    }

                    if (!$targetWindowId) {
                        // Buat window baru:
                        // Jika semua window aktif sudah diisi juri ini, atau belum ada window aktif.
                        $targetWindowId = DB::table('score_windows')->insertGetId([
                            'match_id'    => $idPertandingan,
                            'round_id'    => $idBabak,
                            'athlete_red' => $match->sudut_merah,   // nama atlet sudut merah
                            'athlete_blue'=> $match->sudut_biru,    // nama atlet sudut biru
                            'technique'   => $technique,
                            'opened'      => 1,
                            'opened_at'   => $currentTime,
                            'close_at'    => null,
                            'status'      => 'open',
                            'awarded'     => 0,
                            'created_at'  => now(),
                        ]);
                    }

                    // Simpan event mentah juri, referensi ke window
                    $newEventId = DB::table('score_events')->insertGetId([
                        'match_id'    => $idPertandingan,
                        'round'       => $idBabak,
                        'athlete'     => $athlete,
                        'judge_id'    => $judgeId,
                        'technique'   => $technique,
                        'score_value' => $scoreValue,
                        'server_time' => $currentTime,
                        'status'      => 'pending',
                        'window_id'   => $targetWindowId,
                        'created_at'  => now(),
                    ]);

                    // Hitung jumlah juri BERBEDA yang sudah input di window ini
                    $distinctJudgesInWindow = DB::table('score_events')
                        ->where('window_id', $targetWindowId)
                        ->where('status', 'pending')
                        ->distinct()
                        ->count('judge_id');

                    // Selesaikan window lebih awal jika semua 3 juri sudah menginput,
                    // ATAU minimal 2 juri berbeda sudah sepakat (sesuai revisi.md poin 3).
                    if ($distinctJudgesInWindow >= 3) {
                        $this->resolveGroup($targetWindowId);
                    } elseif ($distinctJudgesInWindow >= 2) {
                        // Minimal 2 juri berbeda → langsung awarded sesuai catatan revisi.md
                        $this->resolveGroup($targetWindowId);
                    }

                    $juriName = match ($petugasPertandingan->posisi) {
                        'juri_1' => 'JURI 1',
                        'juri_2' => 'JURI 2',
                        'juri_3' => 'JURI 3',
                        default  => 'JURI'
                    };
                    $techName  = $technique === 'punch' ? 'pukulan' : 'tendangan';
                    $sudutName = $athlete === 'red' ? 'merah' : 'biru';

                    DB::table('log_activity_juri')->insert([
                        'id_pertandingan' => $idPertandingan,
                        'id_juri'         => $judgeId,
                        'id_babak'        => $idBabak,
                        'id_score_event'  => $newEventId,
                        'action'          => 'INPUT_NILAI',
                        'description'     => "$juriName memasukkan nilai $techName untuk sudut $sudutName",
                        'created_at'      => now(),
                    ]);

                    error_log("=== [ACTION] $juriName mencet $techName untuk sudut $sudutName ===");

                    return Response::buildSuccess(['window_id' => $targetWindowId, 'score_event_id' => $newEventId], 200, 'Berhasil mencatat nilai');
                });
            } finally {
                // Selalu release lock agar juri berikutnya bisa masuk
                $lock->release();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Menyelesaikan penilaian untuk suatu window (Sah / Tidak Sah).
     * Menerima $windowId (BIGINT) — ID dari tabel score_windows.
     */
    public function resolveGroup(int $windowId): void
    {
        $funcName = $this->className . '.resolveGroup';

        try {
            DB::transaction(function () use ($windowId) {
                // Kunci window agar tidak diproses ganda oleh proses lain
                $window = DB::table('score_windows')
                    ->where('id', $windowId)
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->first();

                if (!$window) {
                    return; // sudah diresolve sebelumnya atau tidak ditemukan
                }

                // Ambil semua event pending di window ini
                $inputs = DB::table('score_events')
                    ->where('window_id', $windowId)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->get();

                if ($inputs->isEmpty()) {
                    return; // sudah diresolve sebelumnya
                }

                $currentTime = microtime(true);

                // Hitung dukungan tiap teknik, satu juri hanya dihitung sekali per teknik
                $techCounts = [];
                $seenJudges = [];
                foreach ($inputs as $input) {
                    if (!isset($seenJudges[$input->technique])) {
                        $seenJudges[$input->technique] = [];
                    }
                    if (!in_array($input->judge_id, $seenJudges[$input->technique])) {
                        $seenJudges[$input->technique][] = $input->judge_id;
                        $techCounts[$input->technique] = ($techCounts[$input->technique] ?? 0) + 1;
                    }
                }

                // Cari teknik dengan dukungan >= 2 juri (minimal 2 dari 3 sepakat)
                $winningTechnique = null;
                foreach ($techCounts as $tech => $count) {
                    if ($count >= 2) {
                        $winningTechnique = $tech;
                        break;
                    }
                }

                if ($winningTechnique !== null) {
                    // KONSENSUS TERCAPAI
                    $winningInput      = $inputs->firstWhere('technique', $winningTechnique);
                    $winningScoreValue = self::SCORE_VALUE_MAP[$winningTechnique];

                    $awardIdDb = DB::table('score_awards')->insertGetId([
                        'match_id'     => $winningInput->match_id,
                        'round'        => $winningInput->round,
                        'athlete'      => $winningInput->athlete,
                        'technique'    => $winningTechnique,
                        'score_value'  => $winningScoreValue,
                        'awarded_time' => $winningInput->server_time,
                        'window_id'    => $windowId,
                        'source'       => 'automatic',
                        'created_at'   => now(),
                    ]);

                    // Catat vote untuk juri yang sepakat (teknik sama) -> dipakai utk tandai "sah"
                    $votedJudges = [];
                    foreach ($inputs as $input) {
                        if ($input->technique === $winningTechnique) {
                            if (!in_array($input->judge_id, $votedJudges)) {
                                $votedJudges[] = $input->judge_id;
                                DB::table('score_award_votes')->insert([
                                    'award_id'       => $awardIdDb,
                                    'judge_id'       => $input->judge_id,
                                    'score_event_id' => $input->id,
                                    'created_at'     => now(),
                                ]);
                            }

                            DB::table('score_events')
                                ->where('id', $input->id)
                                ->update(['status' => 'consumed']);
                        } else {
                            DB::table('score_events')
                                ->where('id', $input->id)
                                ->update(['status' => 'expired']);
                        }
                    }

                    // Update status window menjadi 'awarded'
                    DB::table('score_windows')
                        ->where('id', $windowId)
                        ->update([
                            'opened'   => 0,
                            'close_at' => $currentTime,
                            'status'   => 'awarded',
                            'awarded'  => 1,
                        ]);

                    // Tambahkan poin ke skor_pertandingan
                    $skorField = $winningInput->athlete === 'red' ? 'skor_merah' : 'skor_biru';

                    $skorRecord = DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $winningInput->match_id)
                        ->lockForUpdate()
                        ->first();

                    if ($skorRecord) {
                        DB::table('skor_pertandingan')
                            ->where('id_pertandingan', $winningInput->match_id)
                            ->update([
                                $skorField   => DB::raw($skorField . ' + ' . $winningScoreValue),
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('skor_pertandingan')->insert([
                            'id_pertandingan' => $winningInput->match_id,
                            $skorField        => $winningScoreValue,
                            'updated_at'      => now(),
                        ]);
                    }

                    $winningTechName  = $winningTechnique === 'punch' ? 'pukulan' : 'tendangan';
                    $winningSudutName = $winningInput->athlete === 'red' ? 'merah' : 'biru';
                    error_log("=== [KONSENSUS] SAH! Poin $winningTechName sudut $winningSudutName bertambah (nilai: $winningScoreValue) ===");
                } else {
                    // KONSENSUS TIDAK TERCAPAI (mis. hanya 1 juri input, atau semua beda teknik)
                    DB::table('score_events')
                        ->where('window_id', $windowId)
                        ->update(['status' => 'expired']);

                    // Update status window menjadi 'expired'
                    DB::table('score_windows')
                        ->where('id', $windowId)
                        ->update([
                            'opened'   => 0,
                            'close_at' => $currentTime,
                            'status'   => 'expired',
                            'awarded'  => 0,
                        ]);

                    error_log("=== [KONSENSUS] GAGAL/EXPIRED! Tidak mencapai kesepakatan juri ===");
                }
            });
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
        }
    }

    /**
     * Memeriksa dan memutuskan window yang sudah melewati batas waktu (expired).
     * Query berbasis tabel score_windows, bukan GROUP BY award_id.
     */
    public function resolveExpiredEvents($matchId = null): void
    {
        $funcName = $this->className . '.resolveExpiredEvents';

        try {
            $currentTime = microtime(true);

            // Ambil semua window yang masih 'open'
            $query = DB::table('score_windows')
                ->where('status', 'open');

            if ($matchId) {
                $query->where('match_id', $matchId);
            }

            $openWindows = $query->get();

            foreach ($openWindows as $window) {
                // Ambil delay_max berdasarkan teknik window
                $kategoriId = array_search($window->technique, self::TECHNIQUE_MAP, true);
                $kategori   = DB::table('kategori_nilai')->where('id', $kategoriId)->first();
                $delayMax   = $kategori ? (float) $kategori->delay_max : self::DEFAULT_DELAY;

                if (($currentTime - $window->opened_at) > $delayMax) {
                    // Window sudah melewati batas waktu → resolve
                    $lockName = "resolve_window_" . $window->id;
                    $lock = \Illuminate\Support\Facades\Cache::lock($lockName, 5); // 5 detik lock

                    if ($lock->get()) {
                        try {
                            $this->resolveGroup($window->id);
                        } finally {
                            $lock->release();
                        }
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

        $idPertandingan = $request->input('id_pertandingan');
        $idBabak        = $request->input('id_babak');
        $juriPosition   = session('juri_position');
        $sudut          = $request->input('sudut');

        if (!$idPertandingan || !$idBabak || !$juriPosition || !in_array($sudut, ['merah', 'biru'], true)) {
            return Response::buildErrorService('Parameter tidak valid');
        }

        $athlete = $sudut === 'merah' ? 'red' : 'blue';

        try {
            return DB::transaction(function () use ($idPertandingan, $idBabak, $juriPosition, $athlete) {
                $petugasPertandingan = DB::table('petugas_pertandingan')
                    ->where('posisi', $juriPosition)
                    ->where('id_pertandingan', $idPertandingan)
                    ->first();

                if (!$petugasPertandingan) {
                    return Response::buildErrorService('Penugasan tidak ditemukan untuk posisi ' . strtoupper(str_replace('_', ' ', $juriPosition)));
                }

                $lastInput = DB::table('score_events')
                    ->where('match_id', $idPertandingan)
                    ->where('round', $idBabak)
                    ->where('judge_id', $petugasPertandingan->id)
                    ->where('athlete', $athlete)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($lastInput) {
                    DB::table('score_events')
                        ->where('id', $lastInput->id)
                        ->update([
                            'status'         => 'deleted',
                            'deleted_by'     => $petugasPertandingan->id,
                            'deleted_at'     => now(),
                            'deleted_reason' => 'Dihapus manual oleh juri'
                        ]);

                    $juriName = match ($petugasPertandingan->posisi) {
                        'juri_1' => 'JURI 1',
                        'juri_2' => 'JURI 2',
                        'juri_3' => 'JURI 3',
                        default  => 'JURI'
                    };
                    $sudutName = $athlete === 'red' ? 'merah' : 'biru';

                    DB::table('log_activity_juri')->insert([
                        'id_pertandingan' => $idPertandingan,
                        'id_juri'         => $petugasPertandingan->id,
                        'id_babak'        => $idBabak,
                        'id_score_event'  => $lastInput->id,
                        'action'          => 'HAPUS_NILAI',
                        'description'     => "$juriName menghapus nilai pending untuk sudut $sudutName",
                        'created_at'      => now(),
                    ]);

                    error_log("=== [ACTION] $juriName menghapus nilai pending untuk sudut $sudutName ===");
                    return Response::buildSuccess(null, 200, 'Berhasil menghapus nilai pending');
                }

                return Response::buildErrorService('Tidak ada nilai pending untuk dihapus');
            });
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getHistory(Request $request): array
    {
        $funcName = $this->className . '.getHistory';

        try {
            $idPertandingan = $request->input('id_pertandingan');
            $this->resolveExpiredEvents($idPertandingan);

            $juriPosition   = session('juri_position');

            $petugasPertandingan = DB::table('petugas_pertandingan')
                ->where('posisi', $juriPosition)
                ->where('id_pertandingan', $idPertandingan)
                ->first();

            if (!$petugasPertandingan) {
                return Response::buildSuccess([
                    'history' => [],
                    'juri'    => ['nama' => 'MENUNGGU PENUGASAN', 'posisi' => strtoupper(str_replace('_', ' ', $juriPosition))],
                    'timer'   => [
                        'time_remaining' => 0,
                        'status'         => 'stopped'
                    ]
                ], 200, 'Berhasil mengambil riwayat (penugasan tidak ditemukan)');
            }

            $petugas = DB::table('data_petugas')->where('id', $petugasPertandingan->id_petugas)->first();

            $events = DB::table('score_events')
                ->where('match_id', $idPertandingan)
                ->where('judge_id', $petugasPertandingan->id)
                ->whereIn('status', ['consumed', 'pending'])
                ->orderBy('server_time', 'asc')
                ->get();

            // Ambil semua vote sekaligus (hindari N+1 query exists() per baris)
            $votedEventIds = DB::table('score_award_votes')
                ->whereIn('score_event_id', $events->pluck('id'))
                ->pluck('score_event_id')
                ->flip();

            $history = $events->map(function ($item) use ($votedEventIds) {
                return [
                    'id'                      => $item->id,
                    'id_pertandingan'         => $item->match_id,
                    'id_babak'                => $item->round,
                    'id_petugas_pertandingan' => $item->judge_id,
                    'sudut'                   => $item->athlete === 'red' ? 'merah' : 'biru',
                    'nilai'                   => $item->score_value,
                    'waktu_input'             => $item->server_time,
                    'status'                  => $item->status,
                    'window_id'               => $item->window_id,
                    'is_sah'                  => $votedEventIds->has($item->id),
                ];
            });

            $juriData = [
                'nama'   => strtoupper($petugas->nama),
                'posisi' => $petugasPertandingan->posisi ? strtoupper(str_replace('_', ' ', $petugasPertandingan->posisi)) : 'JURI'
            ];

            $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $idPertandingan, [
                'round'          => 1,
                'time_remaining' => 120,
                'status'         => 'stopped'
            ]);

            return Response::buildSuccess([
                'history' => $history->toArray(),
                'juri'    => $juriData,
                'timer'   => [
                    'time_remaining' => $timerState['time_remaining'] ?? 0,
                    'status'         => $timerState['status'] ?? 'stopped'
                ]
            ], 200, 'Berhasil mengambil riwayat');
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }
}