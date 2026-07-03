<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Entities\ResponseEntity;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Http\Usecases\UserUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected UserUsecase $usecase;

    protected array $page = [
        "route" => "user",
        "title" => "Data Pengguna",
    ];

    protected string $baseRedirect;

    public function __construct(UserUsecase $usecase)
    {
        $this->usecase = $usecase;
        $this->baseRedirect = "operator/" . $this->page['route'];
    }

    /**
     * List User
     */
    public function index(): View|Response
    {
        $data = $this->usecase->getAll();

        return view("Operator.users.index", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
        ]);
    }

    /**
     * Form Add User
     */
    public function add(): View|Response
    {
        return view("Operator.users.add", [
            'page' => $this->page,
        ]);
    }

    /**
     * Create User
     */
    public function doCreate(Request $request): JsonResponse
    {
        $process = $this->usecase->create($request);

        if (empty($process['error'])) {

            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_CREATED,
                "redirect" => $this->baseRedirect
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->baseRedirect
        ]);
    }

    /**
     * Form Update User
     */
    public function update(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {

            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }

        return view("Operator.users.update", [
            'data' => (object) $data['data'],
            'page' => $this->page,
        ]);
    }

    /**
     * Update User
     */
    public function doUpdate(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->update($request, $id);

        if (empty($process['error'])) {

            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_UPDATED,
                "redirect" => $this->baseRedirect
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->baseRedirect
        ]);
    }

    /**
     * Delete User
     */
    public function doDelete(int $id): JsonResponse
    {
        $process = $this->usecase->delete(id: $id);

        if (empty($process['error'])) {

            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_DELETED,
                "redirect" => $this->baseRedirect
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->baseRedirect
        ]);
    }

    /**
     * Reset Password
     */
    public function resetPassword(int $id): JsonResponse
    {
        $process = $this->usecase->resetPassword(id: $id);

        if (empty($process['error'])) {

            return response()->json([
                "success" => true,
                "message" => "Password berhasil direset",
                "redirect" => $this->baseRedirect
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->baseRedirect
        ]);
    }
}
