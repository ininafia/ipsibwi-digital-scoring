<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Usecases\PetugasUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PetugasController extends Controller
{
    protected string $className;

    protected PetugasUsecase $usecase;

    public function __construct()
    {
        $this->className = self::class;

        $this->usecase = new PetugasUsecase();
    }

    /**
     * =========================
     * AUTH CHECK
     * =========================
     */
    private function authCheck(): ?RedirectResponse
    {
        if (!session('user_id')) {

            return redirect()->route('login');
        }

        if (session('role') != 1) {

            abort(403, 'Akses ditolak');
        }

        return null;
    }

    /**
     * =========================
     * CHECK RESPONSE STATUS
     * =========================
     */
    private function isSuccess(array|null $result): bool
    {
        return is_array($result)
            && isset($result['success'])
            && $result['success'] === true;
    }

    /**
     * =========================
     * LIST DATA PETUGAS
     * =========================
     */
    public function index(): View|Response|RedirectResponse
    {
        if ($r = $this->authCheck()) {

            return $r;
        }

        $result = $this->usecase->getAll();

        return view('Operator.petugas.list', [

            'list' => $result['data']['list'] ?? [],

        ]);
    }

    /**
     * =========================
     * FORM ADD PETUGAS
     * =========================
     */
    public function addPetugas(): View|Response|RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        return view('Operator.petugas.add-petugas');
    }

    /**
     * =========================
     * STORE PETUGAS
     * =========================
     */
    public function storePetugas(
        Request $request
    ): RedirectResponse {

        if ($r = $this->authCheck()) {
            return $r;
        }

        $result = $this->usecase->create($request);

        if (
            empty($result['success'])
            || $result['success'] !== true
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $result['message']
                        ?? 'Terjadi kesalahan'
                );
        }

        return redirect()
            ->route('operator.tanding.add-petugas')
            ->with(
                'success',
                $result['message']
                    ?? 'Berhasil menambahkan data'
            );
    }

    /**
     * =========================
     * FORM EDIT PETUGAS
     * =========================
     */
    public function editPetugas(
        int $id
    ): View|Response|RedirectResponse {

        if ($r = $this->authCheck()) {

            return $r;
        }

        $result = $this->usecase->getByID($id);

        if (!$this->isSuccess($result)) {

            return redirect()
                ->route('operator.petugas.data')
                ->with(
                    'error',
                    $result['message']
                        ?? 'Data tidak ditemukan'
                );
        }

        return view('Operator.petugas.edit-petugas', [

            'petugas' => $result['data'] ?? [],

        ]);
    }

    /**
     * =========================
     * UPDATE PETUGAS
     * =========================
     */
    public function doEditPetugas(
        Request $request,
        int $id
    ): RedirectResponse {

        if ($r = $this->authCheck()) {

            return $r;
        }

        $result = $this->usecase->update(
            $request,
            $id
        );

        if (!$this->isSuccess($result)) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $result['message']
                        ?? 'Gagal memperbarui data'
                );
        }

        return redirect()
            ->route('operator.petugas.data')
            ->with(
                'success',
                $result['message']
                    ?? 'Berhasil memperbarui data'
            );
    }

    /**
     * =========================
     * DELETE PETUGAS
     * =========================
     */
    public function deletePetugas(
        int $id
    ): RedirectResponse {

        if ($r = $this->authCheck()) {

            return $r;
        }

        $result = $this->usecase->delete($id);

        if (!$this->isSuccess($result)) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $result['message']
                        ?? 'Gagal menghapus data'
                );
        }

        return redirect()
            ->route('operator.petugas.data')
            ->with(
                'success',
                $result['message']
                    ?? 'Berhasil menghapus data'
            );
    }
}
