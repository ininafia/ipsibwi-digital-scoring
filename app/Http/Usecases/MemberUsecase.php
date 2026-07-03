<?php

namespace App\Http\Usecases;

use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MemberUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "MemberUsecase";
    }

    /**
     * Get All Atlet
     */
    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $page = $filterData['page'] ?? 1;
        $limit = $filterData['limit'] ?? 10;
        $filterName = $filterData['filter_name'] ?? "";

        try {

            $data = DB::table('atlet as a')
                ->leftJoin('kontingen as k', 'k.id', '=', 'a.id_kontingen');

            if (!empty($filterName)) {
                $data = $data->where('a.nama', 'like', '%' . $filterName . '%');
            }

            $fields = [
                'a.*',
                'k.nama_kontingen',
                'k.jenis'
            ];

            $data = $data
                ->orderBy('a.id', 'desc')
                ->paginate($limit, $fields)
                ->appends(request()->query());

            return Response::buildSuccess([
                'list' => $data,
                'pagination' => [
                    'current_page' => (int) $page,
                    'limit' => (int) $limit,
                    'payload' => $filterData
                ]
            ], ResponseEntity::HTTP_SUCCESS);

        } catch (\Exception $e) {

            Log::error($e->getMessage(), [
                'func_name' => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get Atlet By ID
     */
    public function getByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {

            $data = DB::table('atlet as a')
                ->leftJoin('kontingen as k', 'k.id', '=', 'a.id_kontingen')
                ->where('a.id', $id)
                ->first([
                    'a.*',
                    'k.nama_kontingen',
                    'k.jenis'
                ]);

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );

        } catch (\Exception $e) {

            Log::error($e->getMessage(), [
                'func_name' => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Create Atlet
     */
    public function create(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'nama' => 'required',
            'id_kontingen' => 'required',
        ]);

        $customAttributes = [
            'nama' => 'Nama Atlet',
            'id_kontingen' => 'Kontingen',
        ];

        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();

        try {

            DB::table('atlet')
                ->insert([
                    'nama' => $data['nama'],
                    'id_kontingen' => $data['id_kontingen'],
                ]);

            DB::commit();

            return Response::buildSuccessCreated(
                ResponseEntity::SUCCESS_MESSAGE_CREATED
            );

        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage(), [
                'func_name' => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Update Atlet
     */
    public function update(Request $data, int $id): array
    {
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'nama' => 'required',
            'id_kontingen' => 'required',
        ]);

        $customAttributes = [
            'nama' => 'Nama Atlet',
            'id_kontingen' => 'Kontingen',
        ];

        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();

        try {

            DB::table('atlet')
                ->where('id', $id)
                ->update([
                    'nama' => $data['nama'],
                    'id_kontingen' => $data['id_kontingen'],
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );

        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage(), [
                'func_name' => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Delete Atlet
     */
    public function delete(int $id): array
    {
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {

            $delete = DB::table('atlet')
                ->where('id', $id)
                ->delete();

            if (!$delete) {

                DB::rollback();

                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );

        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage(), [
                'func_name' => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Count Atlet
     */
    public function getCount(): array
    {
        $funcName = $this->className . ".getCount";

        try {

            $data = DB::table('atlet')->count();

            return Response::buildSuccess([
                'count' => $data
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage(), [
                'func_name' => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}