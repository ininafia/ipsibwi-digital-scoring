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

        $catatanFinalisasi = $resultData['catatan_finalisasi'] ?? null;

        // BUG-1 FIX: Resolve expired events DI LUAR transaksi utama.
        // Memanggil resolveExpiredEvents() di dalam beginTransaction() menyebabkan
        // nested transaction (MySQL tidak mendukung secara nyata) yang bisa
        // membuat commit/rollback berperilaku tidak terduga.
        $juriUsecase = new \App\Http\Usecases\JuriUsecase();
        $juriUsecase->resolveExpiredEvents($id);

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

            // Cek apakah masih ada score_events pending setelah resolusi

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

            // Validasi timer dan syarat finalisasi
            $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $id, [
                'round' => 1,
                'time_remaining' => 120,
                'status' => 'stopped',
            ]);

            if ($jenisKemenangan === 'angka') {
                if (
                    (int) $timerState['round'] !== 3 ||
                    (int) $timerState['time_remaining'] > 0 ||
                    $timerState['status'] !== 'stopped'
                ) {
                    DB::rollback();
                    return Response::buildErrorService(
                        'Kemenangan angka hanya dapat difinalisasi setelah ronde 3 selesai dan timer berhenti.'
                    );
                }
            } else {
                if (empty($resultData['sudut_pemenang'])) {
                    DB::rollback();
                    return Response::buildErrorService('Sudut pemenang wajib diisi untuk jenis kemenangan ini.');
                }
                if (empty($catatanFinalisasi)) {
                    DB::rollback();
                    return Response::buildErrorService('Catatan finalisasi atau alasan wajib diisi untuk jenis kemenangan ini.');
                }
                if (empty($resultData['role_pengesah']) && empty($resultData['pengesah'])) {
                    DB::rollback();
                    return Response::buildErrorService('Role yang mengesahkan wajib ada untuk jenis kemenangan ini.');
                }
            }

            // 5. Ambil skor dari database (BUKAN dari input client)
            $scoreRecord = DB::table('skor_pertandingan')->where('id_pertandingan', $id)->first();
            $skorBiru = $scoreRecord ? (int) $scoreRecord->skor_biru : 0;
            $skorMerah = $scoreRecord ? (int) $scoreRecord->skor_merah : 0;

            // 6. Hitung pemenang
            $sudutPemenang = null;
            $namaPemenang = null;

            if ($jenisKemenangan === 'angka') {
                // Untuk kemenangan angka mutlak hitung dari skor
                if ($skorBiru > $skorMerah) {
                    $sudutPemenang = 'biru';
                    $namaPemenang = $match->sudut_biru;
                } elseif ($skorMerah > $skorBiru) {
                    $sudutPemenang = 'merah';
                    $namaPemenang = $match->sudut_merah;
                } else {
                    DB::rollback();
                    return Response::buildErrorService(
                        'Skor seri! Pertandingan seri memerlukan keputusan lanjutan, tidak bisa difinalisasi secara otomatis.'
                    );
                }
            } else {
                // Untuk kemenangan jenis lain (diskualifikasi, WMP, dll) pemenang ditentukan oleh operator dari form
                $sudutPemenang = $resultData['sudut_pemenang'] ?? null;
                if (!in_array($sudutPemenang, ['merah', 'biru'], true)) {
                    DB::rollback();
                    return Response::buildErrorService(
                        'Untuk kemenangan selain angka, sudut pemenang harus ditentukan secara manual dari sistem.'
                    );
                }
                $namaPemenang = $sudutPemenang === 'merah' ? $match->sudut_merah : $match->sudut_biru;
            }

            // 7. Simpan hasil finalisasi
            $updated = DB::table('pertandingan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'status'              => 'finished',
                    'winner_corner'       => $sudutPemenang,
                    'winner_name'         => $namaPemenang,
                    'winning_method'      => $jenisKemenangan,
                    'final_score_biru'    => $skorBiru,
                    'final_score_merah'   => $skorMerah,
                    'catatan_finalisasi'  => $catatanFinalisasi,
                    'finalized_by'        => session('user_id'),
                    'finalized_at'        => now(),
                    'updated_by'          => session('user_id'),
                    'updated_at'          => now(),
                ]);

            if (!$updated) {
                DB::rollback();
                throw new Exception("FAILED FINALIZE MATCH");
            }

            // Hitung akurasi juri secara otomatis
            $akurasiUsecase = new \App\Http\Usecases\AkurasiJuriUsecase();
            $akurasiUsecase->calculateForMatch($id);

            DB::commit();

            // BUG-9 FIX: Bersihkan cache timer setelah finalisasi agar
            // pertandingan berikutnya tidak menampilkan waktu sisa yang lama.
            \Illuminate\Support\Facades\Cache::forget('current_timer_state_' . $id);

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