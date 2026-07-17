<?php

namespace App\Http\Usecases;

use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PertandinganUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "PertandinganUsecase";
    }

    /*
    |--------------------------------------------------------------------------
    | GET BY ID
    | Digunakan untuk mengambil satu data pertandingan pada halaman play.
    |--------------------------------------------------------------------------
    */
    public function getByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {
            $data = DB::table('pertandingan')
                ->whereNull('deleted_at')
                ->where('id', $id)
                ->first([
                    'id',
                    'nomor',
                    'partai',
                    'gelanggang',
                    'kelas',
                    'golongan',
                    'jenis_kelamin',
                    'sudut_biru',
                    'kontingen_biru',
                    'sudut_merah',
                    'kontingen_merah',
                    'status',
                    'created_at',
                    'updated_at',
                ]);

            if (!$data) {
                return Response::buildErrorService(
                    ResponseEntity::getNotFoundMsg('Pertandingan'),
                    ResponseEntity::HTTP_NOT_FOUND
                );
            }

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET ACTIVE MATCH
    | Mengambil pertandingan yang sedang berlangsung (status = 'playing').
    |--------------------------------------------------------------------------
    */
    public function getActiveMatch(): array
    {
        $funcName = $this->className . ".getActiveMatch";

        try {
            $data = DB::table('pertandingan')
                ->whereNull('deleted_at')
                ->where('status', 'playing')
                ->first([
                    'id',
                    'nomor',
                    'partai',
                    'gelanggang',
                    'kelas',
                    'golongan',
                    'jenis_kelamin',
                    'sudut_biru',
                    'kontingen_biru',
                    'sudut_merah',
                    'kontingen_merah',
                    'status',
                    'created_at',
                    'updated_at',
                ]);

            if (!$data) {
                return Response::buildErrorService(
                    "Tidak ada pertandingan yang sedang berlangsung",
                    ResponseEntity::HTTP_NOT_FOUND
                );
            }

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    | Mengubah status pertandingan, misal dari 'waiting' -> 'playing'.
    |--------------------------------------------------------------------------
    */
    public function updateStatus(int $id, string $status): array
    {
        $funcName = $this->className . ".updateStatus";

        $allowedStatus = ['waiting', 'playing', 'finished', 'final'];

        if (!in_array($status, $allowedStatus)) {
            return Response::buildErrorService('Status tidak valid');
        }

        DB::beginTransaction();

        try {
            $updated = DB::table('pertandingan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'status'     => $status,
                    'updated_by' => session('user_id'),
                    'updated_at' => now(),
                ]);

            if (!$updated) {
                DB::rollback();
                throw new Exception("FAILED UPDATE STATUS");
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET FINISHED MATCHES
    | Mengambil data pertandingan yang sudah selesai beserta skor
    |--------------------------------------------------------------------------
    */
    public function getFinished(): array
    {
        $funcName = $this->className . ".getFinished";

        try {
            $data = DB::table('pertandingan')
                ->leftJoin('skor_pertandingan', 'pertandingan.id', '=', 'skor_pertandingan.id_pertandingan')
                ->where('pertandingan.status', 'finished')
                ->whereNull('pertandingan.deleted_at')
                ->orderBy('pertandingan.nomor', 'asc')
                ->get([
                    'pertandingan.id',
                    'pertandingan.nomor',
                    'pertandingan.partai',
                    'pertandingan.gelanggang',
                    'pertandingan.kelas',
                    'pertandingan.golongan',
                    'pertandingan.jenis_kelamin',
                    'pertandingan.sudut_biru',
                    'pertandingan.kontingen_biru',
                    'pertandingan.sudut_merah',
                    'pertandingan.kontingen_merah',
                    'pertandingan.status',
                    'skor_pertandingan.skor_merah',
                    'skor_pertandingan.skor_biru',
                ]);

            return Response::buildSuccess(
                data: ['list' => collect($data)->toArray()]
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FINALIZE MATCH
    | Finalisasi pertandingan beserta hasil kemenangannya.
    | Semua validasi dilakukan server-side, pemenang dihitung dari database.
    |--------------------------------------------------------------------------
    */
    public function finalizeMatch(int $id, array $resultData): array
    {
        $funcName = $this->className . ".finalizeMatch";

        // Daftar jenis kemenangan yang valid
        $allowedMethods = ['angka', 'teknik', 'mutlak', 'wmp', 'disk', 'undur_diri'];

        $jenisKemenangan = $resultData['jenis_kemenangan'] ?? null;
        if (!$jenisKemenangan || !in_array($jenisKemenangan, $allowedMethods, true)) {
            return Response::buildErrorService(
                'Jenis kemenangan tidak valid. Pilihan: ' . implode(', ', $allowedMethods)
            );
        }

        DB::beginTransaction();

        try {
            // 1. Kunci baris pertandingan dan validasi status
            $match = DB::table('pertandingan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (!$match) {
                DB::rollback();
                return Response::buildErrorService('Pertandingan tidak ditemukan');
            }

            if ($match->status !== 'playing') {
                DB::rollback();
                return Response::buildErrorService(
                    'Pertandingan tidak bisa difinalisasi karena status saat ini: ' . $match->status
                );
            }

            // 2. Cek timer harus sudah berhenti
            $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $id, ['status' => 'stopped']);
            if ($timerState['status'] === 'playing') {
                DB::rollback();
                return Response::buildErrorService(
                    'Timer masih berjalan. Hentikan timer terlebih dahulu sebelum finalisasi.'
                );
            }

            // 3. Resolve semua pending score events untuk pertandingan ini
            $juriUsecase = new \App\Http\Usecases\JuriUsecase();
            $juriUsecase->resolveExpiredEvents();

            // 4. Cek apakah masih ada score_events pending setelah resolusi
            $pendingCount = DB::table('score_events')
                ->where('match_id', $id)
                ->where('status', 'pending')
                ->count();

            if ($pendingCount > 0) {
                DB::rollback();
                return Response::buildErrorService(
                    "Masih ada {$pendingCount} input juri yang berstatus pending. Tunggu hingga semua input terproses."
                );
            }

            // 5. Ambil skor dari database (BUKAN dari input client)
            $scoreRecord = DB::table('skor_pertandingan')->where('id_pertandingan', $id)->first();
            $skorBiru = $scoreRecord ? (int) $scoreRecord->skor_biru : 0;
            $skorMerah = $scoreRecord ? (int) $scoreRecord->skor_merah : 0;

            // 6. Hitung pemenang dari skor database
            $sudutPemenang = null;
            $namaPemenang = null;

            if ($skorBiru > $skorMerah) {
                $sudutPemenang = 'biru';
                $namaPemenang = $match->sudut_biru;
            } elseif ($skorMerah > $skorBiru) {
                $sudutPemenang = 'merah';
                $namaPemenang = $match->sudut_merah;
            }
            // Jika seri (skor sama), sudut_pemenang dan nama_pemenang tetap null

            // 7. Simpan hasil finalisasi
            $updated = DB::table('pertandingan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'status'            => 'finished',
                    'winner_corner'     => $sudutPemenang,
                    'winner_name'       => $namaPemenang,
                    'winning_method'    => $jenisKemenangan,
                    'final_score_biru'  => $skorBiru,
                    'final_score_merah' => $skorMerah,
                    'finalized_by'      => session('user_id'),
                    'finalized_at'      => now(),
                    'updated_by'        => session('user_id'),
                    'updated_at'        => now(),
                ]);

            if (!$updated) {
                DB::rollback();
                throw new Exception("FAILED FINALIZE MATCH");
            }

            // Hitung akurasi juri secara otomatis
            $akurasiUsecase = new \App\Http\Usecases\AkurasiJuriUsecase();
            $akurasiUsecase->calculateForMatch($id);

            DB::commit();

            return Response::buildSuccess(
                message: "Pertandingan berhasil difinalisasi"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}