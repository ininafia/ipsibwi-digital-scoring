<?php

namespace App\Http\Usecases;

use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "UserUsecase";
    }

    /**
     * Get All User
     */
    public function getAll(): array
    {
        try {
            $data = DB::table('users')
                ->orderBy('id', 'desc')
                ->paginate(10);

            return Response::buildSuccess([
                'list' => $data
            ]);
        } catch (\Exception $e) {
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get User By ID
     */
    public function getByID(int $id): array
    {
        try {
            $data = DB::table('users')
                ->where('id', $id)
                ->first();

            return Response::buildSuccess([
                'data' => $data ?? []
            ]);

        } catch (\Exception $e) {
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Create User
     */
    public function create(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|unique:users,username',
            'password'    => 'required|min:6',
            'access_type' => 'required|integer',
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            DB::table('users')->insert([
                'username'    => $request['username'],
                'password'    => Hash::make($request['password']),
                'access_type' => $request['access_type'],
                'is_active'   => 1,
                'created_at'  => now(),
            ]);

            DB::commit();

            return Response::buildSuccessCreated(
                ResponseEntity::SUCCESS_MESSAGE_CREATED
            );

        } catch (\Exception $e) {
            DB::rollback();
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Update User
     */
    public function update(Request $request, int $id): array
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required',
            'access_type' => 'required|integer',
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'username'    => $request['username'],
                    'access_type' => $request['access_type'],
                    'updated_at'  => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );

        } catch (\Exception $e) {
            DB::rollback();
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Delete User (soft delete)
     */
    public function delete(int $id): array
    {
        DB::beginTransaction();

        try {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'is_active'  => 0,
                    'deleted_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );

        } catch (\Exception $e) {
            DB::rollback();
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Reset Password (single user by ID)
     */
    public function resetPassword(int $id): array
    {
        DB::beginTransaction();

        try {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'password'   => Hash::make('123456'),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess([
                'message' => 'Password berhasil direset ke 123456'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * SEED DEFAULT USERS
     * - Jika user BELUM ada → INSERT dengan password Bcrypt
     * - Jika user SUDAH ada → UPDATE password ke Bcrypt
     *   (fix: sebelumnya skip jika exists, sehingga password lama non-Bcrypt tidak pernah diupdate)
     */
    public function seedDefaultUsers(): array
    {
        DB::beginTransaction();

        try {
            $users = [
                ['username' => 'operator', 'password' => '123456', 'access_type' => 1],
                ['username' => 'ketua',    'password' => '123456', 'access_type' => 2],
                ['username' => 'dewan',    'password' => '123456', 'access_type' => 3],
                ['username' => 'timer',    'password' => '123456', 'access_type' => 4],
                ['username' => 'juri1',    'password' => '123456', 'access_type' => 5],
                ['username' => 'juri2',    'password' => '123456', 'access_type' => 6],
                ['username' => 'juri3',    'password' => '123456', 'access_type' => 7],
            ];

            foreach ($users as $user) {

                $exists = DB::table('users')
                    ->where('username', $user['username'])
                    ->exists();

                if (!$exists) {
                    // User belum ada → INSERT
                    DB::table('users')->insert([
                        'username'    => $user['username'],
                        'password'    => Hash::make($user['password']),
                        'access_type' => $user['access_type'],
                        'is_active'   => 1,
                        'created_at'  => now(),
                    ]);
                } else {
                    // User sudah ada → UPDATE password ke Bcrypt
                    DB::table('users')
                        ->where('username', $user['username'])
                        ->update([
                            'password'    => Hash::make($user['password']),
                            'access_type' => $user['access_type'],
                            'is_active'   => 1,
                            'updated_at'  => now(),
                        ]);
                }
            }

            DB::commit();

            return Response::buildSuccess([
                'message' => 'User default berhasil dibuat / diperbarui'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return Response::buildErrorService($e->getMessage());
        }
    }
}