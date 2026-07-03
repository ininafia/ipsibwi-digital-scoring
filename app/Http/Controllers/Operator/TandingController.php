<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Usecases\TandingUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TandingController extends Controller
{
    protected string $className;
    protected TandingUsecase $usecase;

    public function __construct()
    {
        $this->className = self::class;
        $this->usecase   = new TandingUsecase();
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
     * LIST DATA
     * =========================
     */
    public function index(): View|Response|RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        $tab    = request('tab', 'waiting');
        $result = $this->usecase->getAll([
            'status' => $tab
        ]);

        return view('Operator.tanding.list', [
            'list' => $result['data']['list'] ?? [],
            'tab'  => $tab,
        ]);
    }

    /**
     * =========================
     * FORM ADD JADWAL
     * =========================
     */
    public function addJadwal(): View|Response|RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        return view('Operator.tanding.add-jadwal');
    }

    /**
     * =========================
     * STORE JADWAL
     * =========================
     */
    public function doAddJadwal(Request $request): RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        $result = $this->usecase->create($request);

        // DEBUG (aktifkan sementara kalau masih error)
        // dd($result);

        if (empty($result['success']) || $result['success'] !== true) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $result['message'] ?? 'Terjadi kesalahan'
                );
        }

        return redirect()
            ->route('operator.tanding.index')
            ->with(
                'success',
                $result['message'] ?? 'Berhasil menambahkan data'
            );
    }

    /**
     * =========================
     * FORM EDIT JADWAL
     * =========================
     */
    public function editJadwal(int $id): View|Response|RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        $result = $this->usecase->getByID($id);

        if (!$this->isSuccess($result)) {
            return redirect()
                ->route('operator.tanding.add-jadwal')
                ->with(
                    'error',
                    $result['message'] ?? 'Data tidak ditemukan'
                );
        }

        return view('Operator.tanding.edit-jadwal', [
            'pertandingan' => $result['data'] ?? [],
        ]);
    }

    /**
     * =========================
     * UPDATE JADWAL
     * =========================
     */
    public function doEditJadwal(
        Request $request,
        int $id
    ): RedirectResponse {
        if ($r = $this->authCheck()) {
            return $r;
        }

        $result = $this->usecase->update($request, $id);

        if (!$this->isSuccess($result)) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $result['message'] ?? 'Gagal memperbarui data'
                );
        }

        return redirect()
            ->route('operator.tanding.add-jadwal')
            ->with(
                'success',
                $result['message'] ?? 'Berhasil memperbarui data'
            );
    }

    /**
     * =========================
     * DELETE JADWAL
     * =========================
     */
    public function deleteJadwal(int $id): RedirectResponse
    {
        if ($r = $this->authCheck()) {
            return $r;
        }

        $result = $this->usecase->delete($id);

        if (!$this->isSuccess($result)) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $result['message'] ?? 'Gagal menghapus data'
                );
        }

        return redirect()
            ->route('operator.tanding.add-jadwal')
            ->with(
                'success',
                $result['message'] ?? 'Berhasil menghapus data'
            );
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

        return view('Operator.tanding.add-petugas');
    }

    /**
     * =========================
     * UPDATE STATUS
     * =========================
     */
    public function updateStatus(
        Request $request,
        int $id
    ): RedirectResponse {
        if ($r = $this->authCheck()) {
            return $r;
        }

        $status = $request->input('status');

        $result = $this->usecase->updateStatus($id, $status);

        if (!$this->isSuccess($result)) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $result['message'] ?? 'Gagal mengubah status'
                );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                $result['message'] ?? 'Status berhasil diperbarui'
            );
    }
}
