<?php

namespace App\Http\Usecases;

use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TandingUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "TandingUsecase";
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL
    |--------------------------------------------------------------------------
    */
    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $status = $filterData['status'] ?? null;

        try {
            $query = DB::table('pertandingan')
                ->whereNull('deleted_at');

            if (!empty($status)) {
                switch ($status) {
                    case 'final':
                        $query->whereIn('status', ['finished', 'final']);
                        break;
                    case 'waiting':
                        $query->whereIn('status', ['waiting', 'playing']);
                        break;
                    case 'playing':
                    case 'finished':
                        $query->where('status', $status);
                        break;
                    default:
                        $query->where('status', 'waiting');
                        break;
                }
            }

            $data = $query->orderBy('nomor', 'asc')->get([
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

           return Response::buildSuccess(
    ['list' => $data],
    200,
    ResponseEntity::SUCCESS_MESSAGE_CREATED
);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET BY ID
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
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'nomor'          => 'required|numeric|min:1',
            'partai'         => [
                'required',
                'numeric',
                'min:1',
                Rule::unique('pertandingan', 'partai')
                    ->whereNull('deleted_at')
            ],
            'gelanggang'     => 'required|string|max:50',
            'kelas'          => 'required|string',
            'golongan'       => 'required|in:pra usia dini,usia dini 1,usia dini 2,pra remaja,remaja,dewasa',
            'jenis_kelamin'  => 'required|in:putra,putri',
            'sudut_biru'     => 'nullable|string|max:100',
            'kontingen_biru' => 'nullable|string|max:100',
            'sudut_merah'    => 'nullable|string|max:100',
            'kontingen_merah'=> 'nullable|string|max:100',
        ], [
            'partai.unique' => 'Partai sudah digunakan.',
        ]);

        $customAttributes = [
            'nomor'          => 'Nomor',
            'partai'         => 'Partai',
            'gelanggang'     => 'Gelanggang',
            'kelas'          => 'Kelas',
            'golongan'       => 'Golongan',
            'jenis_kelamin'  => 'Jenis Kelamin',
            'sudut_biru'     => 'Sudut Biru',
            'kontingen_biru' => 'Kontingen Biru',
            'sudut_merah'    => 'Sudut Merah',
            'kontingen_merah'=> 'Kontingen Merah',
        ];

        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();

        try {
            DB::table('pertandingan')->insert([
                'nomor'          => $data['nomor'],
                'partai'         => $data['partai'],
                'gelanggang'     => $data['gelanggang'],
                'kelas'          => $data['kelas'],
                'golongan'       => $data['golongan'],
                'jenis_kelamin'  => $data['jenis_kelamin'],
                'sudut_biru'     => $data['sudut_biru']     ?? null,
                'kontingen_biru' => $data['kontingen_biru'] ?? null,
                'sudut_merah'    => $data['sudut_merah']    ?? null,
                'kontingen_merah'=> $data['kontingen_merah']?? null,
                'status'         => 'waiting',
                'created_by'     => session('user_id'),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::commit();

            return Response::buildSuccessCreated();
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $data, int $id): array
    {
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'nomor'          => 'required|numeric|min:1',
            'partai'         => [
                'required',
                'numeric',
                'min:1',
                Rule::unique('pertandingan', 'partai')
                    ->ignore($id)
                    ->whereNull('deleted_at')
            ],
            'gelanggang'     => 'required|string|max:50',
            'kelas'          => 'required|string',
            'golongan'       => 'required|in:pra usia dini,usia dini 1,usia dini 2,pra remaja,remaja,dewasa',
            'jenis_kelamin'  => 'required|in:putra,putri',
            'sudut_biru'     => 'nullable|string|max:100',
            'kontingen_biru' => 'nullable|string|max:100',
            'sudut_merah'    => 'nullable|string|max:100',
            'kontingen_merah'=> 'nullable|string|max:100',
        ], [
            'partai.unique' => 'Partai sudah digunakan.',
        ]);

        $customAttributes = [
            'nomor'          => 'Nomor',
            'partai'         => 'Partai',
            'gelanggang'     => 'Gelanggang',
            'kelas'          => 'Kelas',
            'golongan'       => 'Golongan',
            'jenis_kelamin'  => 'Jenis Kelamin',
            'sudut_biru'     => 'Sudut Biru',
            'kontingen_biru' => 'Kontingen Biru',
            'sudut_merah'    => 'Sudut Merah',
            'kontingen_merah'=> 'Kontingen Merah',
        ];

        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();

        try {
            $updated = DB::table('pertandingan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'nomor'          => $data['nomor'],
                    'partai'         => $data['partai'],
                    'gelanggang'     => $data['gelanggang'],
                    'kelas'          => $data['kelas'],
                    'golongan'       => $data['golongan'],
                    'jenis_kelamin'  => $data['jenis_kelamin'],
                    'sudut_biru'     => $data['sudut_biru']      ?? null,
                    'kontingen_biru' => $data['kontingen_biru']  ?? null,
                    'sudut_merah'    => $data['sudut_merah']     ?? null,
                    'kontingen_merah'=> $data['kontingen_merah'] ?? null,
                    'updated_by'     => session('user_id'),
                    'updated_at'     => now(),
                ]);

            if (!$updated) {
                DB::rollback();
                throw new Exception("FAILED UPDATE DATA");
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
    | DELETE (SOFT DELETE)
    |--------------------------------------------------------------------------
    */
    public function delete(int $id): array
    {
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {
            $deleted = DB::table('pertandingan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => session('user_id'),
                ]);

            if ($deleted) {
                // Hapus data log, skor, juri, petugas terkait agar bersih (Cascade Delete)
                $scoreEventIds = DB::table('score_events')->where('match_id', $id)->pluck('id');
                DB::table('score_award_votes')->whereIn('score_event_id', $scoreEventIds)->delete();
                DB::table('score_events')->where('match_id', $id)->delete();
                DB::table('score_awards')->where('match_id', $id)->delete();
                DB::table('log_activity_juri')->where('id_pertandingan', $id)->delete();
                DB::table('skor_pertandingan')->where('id_pertandingan', $id)->delete();
                DB::table('petugas_pertandingan')->where('id_pertandingan', $id)->delete();
            }

            if (!$deleted) {
                DB::rollback();
                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();

            // BUG FIX: Siarkan perubahan sistem agar semua monitor/juri memuat ulang dan menghapus timer pertandingan ini
            event(new \App\Events\SystemStateChanged());

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */
    public function updateStatus(int $id, string $status): array
    {
        $funcName = $this->className . ".updateStatus";

        $allowedStatus = ['waiting', 'playing', 'finished', 'final'];

        if (!in_array($status, $allowedStatus)) {
            return Response::buildErrorService('Status tidak valid');
        }

        $exists = DB::table('pertandingan')->where('id', $id)->first();
        if (!$exists) {
            return Response::buildErrorService('Pertandingan tidak ditemukan');
        }

        if ($status === 'playing') {
            $alreadyPlaying = DB::table('pertandingan')
                ->where('status', 'playing')
                ->whereNull('deleted_at')
                ->where('id', '!=', $id)
                ->first();

            if ($alreadyPlaying) {
                return Response::buildErrorService(
                    "Masih ada pertandingan yang sedang berlangsung (Partai " . str_pad($alreadyPlaying->partai, 3, '0', STR_PAD_LEFT) . "). Selesaikan atau kembalikan ke waiting list terlebih dahulu."
                );
            }
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

            event(new \App\Events\SystemStateChanged());

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}