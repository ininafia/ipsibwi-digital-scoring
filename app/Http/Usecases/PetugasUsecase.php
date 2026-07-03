<?php

namespace App\Http\Usecases;

use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PetugasUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "PetugasUsecase";
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL
    |--------------------------------------------------------------------------
    */
    public function getAll(): array
    {
        $funcName = $this->className . ".getAll";

        try {

            $data = DB::table('data_petugas')
                ->join('users', 'data_petugas.id_user', '=', 'users.id')
                ->whereNull('users.deleted_at')
                ->orderBy('data_petugas.id', 'desc')
                ->get([
                    'data_petugas.id',
                    'data_petugas.nama',
                    DB::raw("CASE users.access_type 
                        WHEN 2 THEN 'Ketua Pertandingan' 
                        WHEN 3 THEN 'Dewan' 
                        WHEN 5 THEN 'Juri' 
                        WHEN 6 THEN 'Wasit' 
                        WHEN 7 THEN 'Delegasi Teknik' 
                        ELSE 'Petugas' END AS tugas"),
                    'users.created_at',
                    'users.updated_at',
                ]);

            return Response::buildSuccess(
                ['list' => $data],
                200,
                ResponseEntity::SUCCESS_MESSAGE_CREATED
            );

        } catch (Exception $e) {

            Log::error($e->getMessage(), [
                'func_name' => $funcName
            ]);

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

            $data = DB::table('petugas')
                ->whereNull('deleted_at')
                ->where('id', $id)
                ->first([
                    'id',
                    'nama',
                    'tugas',
                    'created_at',
                    'updated_at',
                ]);

            if (!$data) {

                return Response::buildErrorService(
                    ResponseEntity::getNotFoundMsg('Petugas'),
                    ResponseEntity::HTTP_NOT_FOUND
                );
            }

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );

        } catch (Exception $e) {

            Log::error($e->getMessage(), [
                'func_name' => $funcName
            ]);

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

            'nama'  => 'required|string|max:100',

            'tugas' => ['required', \Illuminate\Validation\Rule::in(['Ketua Pertandingan', 'Delegasi Teknik', 'Dewan', 'Wasit', 'Juri'])],

        ]);

        $customAttributes = [

            'nama'  => 'Nama Petugas',

            'tugas' => 'Tugas',

        ];

        $validator->setAttributeNames($customAttributes);

        $validator->validate();

        DB::beginTransaction();

        try {

            $roleMap = [
                'Ketua Pertandingan' => 2,
                'Dewan'              => 3,
                'Juri'               => 5,
                'Wasit'              => 6,
                'Delegasi Teknik'    => 7,
            ];
            $tugasStr = trim($data['tugas']);
            $roleId = $roleMap[$tugasStr] ?? 5;

            $username = strtolower(str_replace(' ', '_', $data['nama'])) . '_' . rand(1000, 9999);

            $userId = DB::table('users')->insertGetId([
                'username'    => $username,
                'password'    => bcrypt('123456'),
                'access_type' => $roleId,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('data_petugas')->insert([
                'nama'    => $data['nama'],
                'id_user' => $userId,
            ]);

            DB::commit();

            return Response::buildSuccessCreated();

        } catch (Exception $e) {

            DB::rollback();

            Log::error($e->getMessage(), [
                'func_name' => $funcName
            ]);

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

            'nama'  => 'required|string|max:100',

            'tugas' => ['required', \Illuminate\Validation\Rule::in(['Ketua Pertandingan', 'Delegasi Teknik', 'Dewan', 'Wasit', 'Juri'])],

        ]);

        $customAttributes = [

            'nama'  => 'Nama Petugas',

            'tugas' => 'Tugas',

        ];

        $validator->setAttributeNames($customAttributes);

        $validator->validate();

        DB::beginTransaction();

        try {

            $petugas = DB::table('data_petugas')->where('id', $id)->first();
            if (!$petugas) {
                DB::rollback();
                throw new Exception("Petugas tidak ditemukan");
            }

            $roleMap = [
                'Ketua Pertandingan' => 2,
                'Dewan'              => 3,
                'Juri'               => 5,
                'Wasit'              => 6,
                'Delegasi Teknik'    => 7,
            ];
            $tugasStr = trim($data['tugas']);
            $roleId = $roleMap[$tugasStr] ?? 5;

            $updatedPetugas = DB::table('data_petugas')
                ->where('id', $id)
                ->update(['nama' => $data['nama']]);

            $updatedUser = DB::table('users')
                ->where('id', $petugas->id_user)
                ->update([
                    'access_type' => $roleId,
                    'updated_at'  => now(),
                ]);

            // Accept if either was updated, or if data was same
            if ($updatedPetugas === false || $updatedUser === false) {
                DB::rollback();
                throw new Exception("FAILED UPDATE DATA");
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );

        } catch (Exception $e) {

            DB::rollback();

            Log::error($e->getMessage(), [
                'func_name' => $funcName
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET DROPDOWN DATA FOR ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    public function getDropdownData(): array
    {
        $funcName = $this->className . ".getDropdownData";

        try {
            // Fetch pertandingan
            $pertandingan = DB::table('pertandingan')
                ->where('status', '!=', 'final')
                ->whereNull('deleted_at')
                ->orderBy('partai', 'asc')
                ->get(['id', 'partai', 'gelanggang']);

            // Fetch data_petugas with their roles
            $petugas = DB::table('data_petugas')
                ->join('users', 'data_petugas.id_user', '=', 'users.id')
                ->get(['data_petugas.id', 'data_petugas.nama', 'users.access_type']);

            return Response::buildSuccess([
                'pertandingan' => collect($pertandingan)->toArray(),
                'petugas'      => collect($petugas)->toArray(),
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET ASSIGNED DATA (FOR DEWAN)
    |--------------------------------------------------------------------------
    */
    public function getAssignedData(): array
    {
        $funcName = $this->className . ".getAssignedData";
        try {
            // Get all matches
            $matches = DB::table('pertandingan')
                ->whereNull('deleted_at')
                ->orderBy('partai', 'asc')
                ->get();
            
            // Get all assignments
            $assignments = DB::table('petugas_pertandingan')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->select('petugas_pertandingan.*', 'data_petugas.nama')
                ->get();

            $grouped = [];
            foreach ($matches as $match) {
                $matchAssignments = $assignments->where('id_pertandingan', $match->id);
                
                // Jika tidak ada petugas sama sekali, bisa di-skip (sesuai janji di implementasi)
                if ($matchAssignments->isEmpty()) {
                    continue;
                }

                $juris = $matchAssignments->where('id_role', 5)->values();

                $grouped[] = [
                    'id' => $match->id,
                    'partai' => str_pad($match->partai, 3, '0', STR_PAD_LEFT),
                    'gelanggang' => $match->gelanggang,
                    'ketua' => $matchAssignments->where('id_role', 2)->first()?->nama ?? '-',
                    'delegasi_teknik' => $matchAssignments->where('id_role', 7)->first()?->nama ?? '-',
                    'dewan' => $matchAssignments->where('id_role', 3)->first()?->nama ?? '-',
                    'juri1' => $juris->get(0)?->nama ?? '-',
                    'juri2' => $juris->get(1)?->nama ?? '-',
                    'juri3' => $juris->get(2)?->nama ?? '-',
                ];
            }

            return Response::buildSuccess(
                data: ['list' => $grouped],
                message: 'Berhasil memuat data penugasan'
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN PETUGAS TO PERTANDINGAN
    |--------------------------------------------------------------------------
    */
    public function assignPetugas(Request $request): array
    {
        $funcName = $this->className . ".assignPetugas";

        $validator = Validator::make($request->all(), [
            'id_pertandingan' => 'required|integer|exists:pertandingan,id',
            'ketua'           => 'nullable|integer|exists:data_petugas,id',
            'delegasi_teknik' => 'nullable|integer|exists:data_petugas,id',
            'dewan'           => 'nullable|integer|exists:data_petugas,id',
            'wasit'           => 'nullable|integer|exists:data_petugas,id',
            'juri1'           => 'nullable|integer|exists:data_petugas,id',
            'juri2'           => 'nullable|integer|exists:data_petugas,id',
            'juri3'           => 'nullable|integer|exists:data_petugas,id',
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            $idPertandingan = $request->input('id_pertandingan');
            $assignments = [
                2 => $request->input('ketua'),            // ketua (role 2)
                7 => $request->input('delegasi_teknik'),  // delegasi_teknik (role 7)
                3 => $request->input('dewan'),            // dewan (role 3)
                6 => $request->input('wasit'),            // wasit (role 6)
                5 => [$request->input('juri1'), $request->input('juri2'), $request->input('juri3')] // juri (role 5)
            ];

            // Optional: delete existing assignments for this pertandingan
            DB::table('petugas_pertandingan')
                ->where('id_pertandingan', $idPertandingan)
                ->delete();

            $inserts = [];
            foreach ($assignments as $roleId => $petugasId) {
                if (is_array($petugasId)) {
                    foreach ($petugasId as $juriId) {
                        if ($juriId) {
                            $inserts[] = [
                                'id_pertandingan' => $idPertandingan,
                                'id_petugas'      => $juriId,
                                'id_role'         => $roleId,
                            ];
                        }
                    }
                } else if ($petugasId) {
                    $inserts[] = [
                        'id_pertandingan' => $idPertandingan,
                        'id_petugas'      => $petugasId,
                        'id_role'         => $roleId,
                    ];
                }
            }

            if (!empty($inserts)) {
                DB::table('petugas_pertandingan')->insert($inserts);
            }

            DB::commit();

            return Response::buildSuccess(
                message: 'Berhasil menyimpan penugasan petugas'
            );

        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function delete(int $id): array
    {
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {

            $petugas = DB::table('data_petugas')->where('id', $id)->first();
            if (!$petugas) {
                return Response::buildErrorService(
                    ResponseEntity::getNotFoundMsg('Data'),
                    ResponseEntity::HTTP_NOT_FOUND
                );
            }

            $deleted = DB::table('users')
                ->where('id', $petugas->id_user)
                ->update([
                    'deleted_at' => now(),
                    'deleted_by' => session('user_id'),
                ]);

            if (!$deleted) {

                DB::rollback();

                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );

        } catch (Exception $e) {

            DB::rollback();

            Log::error($e->getMessage(), [
                'func_name' => $funcName
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}