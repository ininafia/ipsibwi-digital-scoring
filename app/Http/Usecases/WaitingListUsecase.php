<?php

namespace App\Http\Usecases;

use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WaitingListUsecase extends Usecase
{
    public string $className = 'WaitingListUsecase';

    /*
    |--------------------------------------------------------------------------
    | GET ALL
    |--------------------------------------------------------------------------
    */

    public function getAll(string $status = 'waiting'): array
    {
        try {

            $query = DB::table(DatabaseEntity::PERTANDINGAN)
                ->whereNull('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | FILTER STATUS
            |--------------------------------------------------------------------------
            */

            switch ($status) {

                case 'final':

                    $query->whereIn('status', [
                        'finished',
                        'final'
                    ]);

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

            /*
            |--------------------------------------------------------------------------
            | GET DATA
            |--------------------------------------------------------------------------
            */

            $data = $query
                ->orderBy('partai', 'asc')
                ->orderBy('nomor', 'asc')
                ->paginate(8);

            return Response::buildSuccess([
                'list' => $data
            ]);
        } catch (Exception $e) {

            Log::error(
                $this->className . '::getAll - ' . $e->getMessage()
            );

            return Response::buildErrorService(
                'Gagal mengambil data waiting list.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FIND BY ID
    |--------------------------------------------------------------------------
    */

    public function findById(int $id): object|null
    {
        return DB::table(DatabaseEntity::PERTANDINGAN)
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | GET BY ID
    |--------------------------------------------------------------------------
    */

    public function getByID(int $id): array
    {
        try {

            $data = $this->findById($id);

            if (!$data) {

                return Response::buildErrorService(
                    'Data pertandingan tidak ditemukan.'
                );
            }

            return Response::buildSuccess(
                data: (array) $data
            );
        } catch (Exception $e) {

            Log::error(
                $this->className . '::getByID - ' . $e->getMessage()
            );

            return Response::buildErrorService(
                'Gagal mengambil detail pertandingan.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    private function validation(Request $data, int|null $id = null)
    {
        return Validator::make($data->all(), [

            'nomor'           => 'required|numeric',

            'partai'          => [
                'required',
                'string',
                'max:10',
                Rule::unique(DatabaseEntity::PERTANDINGAN, 'partai')
                    ->ignore($id)
                    ->whereNull('deleted_at')
            ],

            'gelanggang'      => 'required|string|max:50',

            'kelas'           => 'required|string|max:50',

            'golongan'        => 'required|string|max:100',

            'jenis_kelamin'   => 'required|string|max:20',

            'sudut_biru'      => 'nullable|string|max:100',

            'kontingen_biru'  => 'nullable|string|max:100',

            'sudut_merah'     => 'nullable|string|max:100',

            'kontingen_merah' => 'nullable|string|max:100',

        ], [

            'nomor.required'         => 'Nomor wajib diisi.',
            'nomor.numeric'          => 'Nomor harus berupa angka.',

            'partai.required'        => 'Partai wajib diisi.',
            'partai.unique'          => 'Partai sudah digunakan.',

            'gelanggang.required'    => 'Gelanggang wajib dipilih.',

            'kelas.required'         => 'Kelas wajib dipilih.',

            'golongan.required'      => 'Golongan wajib dipilih.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(Request $data): array
    {
        $validator = $this->validation($data);

        if ($validator->fails()) {

            return Response::buildErrorService(
                $validator->errors()->first()
            );
        }

        DB::beginTransaction();

        try {

            DB::table(DatabaseEntity::PERTANDINGAN)
                ->insert([

                    'nomor'           => $data->nomor,
                    'partai'          => $data->partai,
                    'gelanggang'      => $data->gelanggang,
                    'kelas'           => $data->kelas,
                    'golongan'        => $data->golongan,
                    'jenis_kelamin'   => $data->jenis_kelamin,

                    'sudut_biru'      => $data->sudut_biru,
                    'kontingen_biru'  => $data->kontingen_biru,

                    'sudut_merah'     => $data->sudut_merah,
                    'kontingen_merah' => $data->kontingen_merah,

                    'status'          => 'waiting',

                    'created_by'      => session('user_id'),
                    'created_at'      => now(),
                ]);

            DB::commit();

            return Response::buildSuccessCreated();
        } catch (Exception $e) {

            DB::rollBack();

            Log::error(
                $this->className . '::create - ' . $e->getMessage()
            );

            return Response::buildErrorService(
                'Gagal menambahkan data pertandingan.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $data, int $id): array
    {
        $validator = $this->validation($data, $id);

        if ($validator->fails()) {

            return Response::buildErrorService(
                $validator->errors()->first()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK DATA
        |--------------------------------------------------------------------------
        */

        $exists = $this->findById($id);

        if (!$exists) {

            return Response::buildErrorService(
                'Data pertandingan tidak ditemukan.'
            );
        }

        DB::beginTransaction();

        try {

            DB::table(DatabaseEntity::PERTANDINGAN)
                ->where('id', $id)
                ->update([

                    'nomor'           => $data->nomor,
                    'partai'          => $data->partai,
                    'gelanggang'      => $data->gelanggang,
                    'kelas'           => $data->kelas,
                    'golongan'        => $data->golongan,
                    'jenis_kelamin'   => $data->jenis_kelamin,

                    'sudut_biru'      => $data->sudut_biru,
                    'kontingen_biru'  => $data->kontingen_biru,

                    'sudut_merah'     => $data->sudut_merah,
                    'kontingen_merah' => $data->kontingen_merah,

                    'updated_by'      => session('user_id'),
                    'updated_at'      => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {

            DB::rollBack();

            Log::error(
                $this->className . '::update - ' . $e->getMessage()
            );

            return Response::buildErrorService(
                'Gagal mengupdate data pertandingan.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(int $id, string $status): array
    {
        $allowedStatus = [
            'waiting',
            'playing',
            'finished',
            'final'
        ];

        /*
        |--------------------------------------------------------------------------
        | VALIDATE STATUS
        |--------------------------------------------------------------------------
        */

        if (!in_array($status, $allowedStatus)) {

            return Response::buildErrorService(
                'Status tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK DATA
        |--------------------------------------------------------------------------
        */

        $exists = $this->findById($id);

        if (!$exists) {

            return Response::buildErrorService(
                'Data pertandingan tidak ditemukan.'
            );
        }

        if ($status === 'playing') {
            $alreadyPlaying = DB::table(DatabaseEntity::PERTANDINGAN)
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

            DB::table(DatabaseEntity::PERTANDINGAN)
                ->where('id', $id)
                ->update([

                    'status'     => $status,

                    'updated_by' => session('user_id'),

                    'updated_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: 'Status berhasil diupdate.'
            );
        } catch (Exception $e) {

            DB::rollBack();

            Log::error(
                $this->className . '::updateStatus - ' . $e->getMessage()
            );

            return Response::buildErrorService(
                'Gagal mengupdate status pertandingan.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): array
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK DATA
        |--------------------------------------------------------------------------
        */

        $exists = $this->findById($id);

        if (!$exists) {

            return Response::buildErrorService(
                'Data pertandingan tidak ditemukan.'
            );
        }

        DB::beginTransaction();

        try {

            DB::table(DatabaseEntity::PERTANDINGAN)
                ->where('id', $id)
                ->update([

                    'deleted_by' => session('user_id'),

                    'deleted_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );
        } catch (Exception $e) {

            DB::rollBack();

            Log::error(
                $this->className . '::delete - ' . $e->getMessage()
            );

            return Response::buildErrorService(
                'Gagal menghapus data pertandingan.'
            );
        }
    }
}
