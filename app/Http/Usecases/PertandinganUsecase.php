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
}