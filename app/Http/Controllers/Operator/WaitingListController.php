<?php

namespace App\Http\Controllers\Operator;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Http\Usecases\WaitingListUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WaitingListController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PROPERTY
    |--------------------------------------------------------------------------
    */

    private readonly string $baseRedirect;

    protected array $page = [

        "route" => "waiting-list",

        "title" => "Waiting List",

        "desc"  => "Daftar Jadwal Pertandingan"

    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCT
    |--------------------------------------------------------------------------
    */

    public function __construct(
        public WaitingListUsecase $usecase,
    ) {

        $this->baseRedirect = 'operator/tanding/waiting-list';
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View|Response
    {
        $tab = $request->get('tab', 'waiting');

        $data = $this->usecase->getAll(
            status: $tab
        );

        return view('Operator.tanding.list', [

            'list' => $data['data']['list'] ?? collect(),

            'tab'  => $tab,

            'page' => $this->page,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PAGE
    |--------------------------------------------------------------------------
    */

    public function add(): View|Response
    {
        return view('Operator.tanding.add-jadwal', [

            'page' => $this->page,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function doCreate(Request $request): RedirectResponse
    {
        $process = $this->usecase->create(
            data: $request
        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (!empty($process['success']) && $process['success'] === true) {

            return redirect()
                ->route('operator.tanding.waiting-list.index')
                ->with(
                    'success',
                    'Jadwal berhasil ditambahkan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                $process['message']
                    ?? ResponseEntity::DEFAULT_ERROR_MESSAGE
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT DATA (AJAX MODAL)
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): JsonResponse
    {
        $data = $this->usecase->getByID(
            id: $id
        );

        /*
        |--------------------------------------------------------------------------
        | DATA NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (empty($data['data'])) {

            return response()->json([

                'success' => false,

                'message' => 'Data tidak ditemukan.'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'data'    => $data['data']

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DO UPDATE
    |--------------------------------------------------------------------------
    */

    public function doUpdate(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {

        $process = $this->usecase->update(

            data: $request,

            id: $id

        );

        /*
        |--------------------------------------------------------------------------
        | AJAX/JSON RESPONSE
        |--------------------------------------------------------------------------
        */
        if ($request->expectsJson() || $request->ajax() || $request->isXmlHttpRequest()) {
            if (!empty($process['success']) && $process['success'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal berhasil diperbarui.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $process['message'] ?? 'Gagal menyimpan data.'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (!empty($process['success']) && $process['success'] === true) {

            return redirect()
                ->route('operator.tanding.waiting-list.index')
                ->with(
                    'success',
                    'Jadwal berhasil diperbarui.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                $process['message']
                    ?? ResponseEntity::DEFAULT_ERROR_MESSAGE
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    public function doUpdateStatus(
        int $id,
        Request $request
    ): JsonResponse {

        $process = $this->usecase->updateStatus(

            id: $id,

            status: $request->input('status')

        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (!empty($process['success']) && $process['success'] === true) {

            return response()->json([

                "success" => true,

                "message" => ResponseEntity::SUCCESS_MESSAGE_UPDATED,

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        return response()->json([

            "success" => false,

            "message" => $process['message']
                ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,

        ], 400);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function doDelete(int $id): JsonResponse
    {
        $process = $this->usecase->delete(
            id: $id
        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (!empty($process['success']) && $process['success'] === true) {

            return response()->json([

                "success" => true,

                "message" => ResponseEntity::SUCCESS_MESSAGE_DELETED,

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        return response()->json([

            "success" => false,

            "message" => $process['message']
                ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,

        ], 400);
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL
    |--------------------------------------------------------------------------
    */

    public function detail(int $id): View|Response|RedirectResponse
    {
        $data = $this->usecase->getByID(
            id: $id
        );

        /*
        |--------------------------------------------------------------------------
        | DATA NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (empty($data['data'])) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Data pertandingan tidak ditemukan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view('Operator.tanding.detail', [

            'page' => $this->page,

            'data' => (object) $data['data'],

        ]);
    }
}