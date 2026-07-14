<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\AuthController;
use App\Http\Controllers\Operator\DashboardController;
use App\Http\Controllers\Operator\PertandinganController;
use App\Http\Controllers\Operator\TandingController;
use App\Http\Controllers\Operator\WaitingListController;
use App\Http\Controllers\TimerController;
Route::get('/', fn() => view('Operator.Landingpage'));
Route::get('/operator', fn() => redirect('/'));

Route::get('/login',  [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'doLogin'])->name('login.process');
Route::get('/logout', [AuthController::class, 'doLogout'])->name('logout');

// KHUSUS LOGIN TIMER, JURI, KETUA, & DEWAN
Route::get('/login/timer', [AuthController::class, 'login'])->name('timer.login.view');
Route::get('/login/juri', [AuthController::class, 'login'])->name('juri.login.view');
Route::get('/login/ketua', [AuthController::class, 'login'])->name('ketua.login.view');
Route::get('/login/dewan', [AuthController::class, 'login'])->name('dewan.login.view');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// GLOBAL AJAX ROUTE (Dapat diakses berbagai role)
Route::get('/operator/monitor-display/data', function () {
    if (!session('user_id')) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    
    $match = \Illuminate\Support\Facades\DB::table('pertandingan')
        ->where('status', 'playing')->whereNull('deleted_at')->first();
    if (!$match) return response()->json(['success' => false]);
    
    $score = \Illuminate\Support\Facades\DB::table('skor_pertandingan')->where('id_pertandingan', $match->id)->first();
    
    // Fetch Timer State from Cache
    $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $match->id, [
        'round' => 1,
        'time_remaining' => 120,
        'status' => 'stopped'
    ]);

    return response()->json([
        'success' => true,
        'match' => [
            'id' => $match->id,
            'partai' => $match->partai ?? '-',
            'sudut_biru' => $match->sudut_biru ?? '-',
            'kontingen_biru' => $match->kontingen_biru ?? '-',
            'sudut_merah' => $match->sudut_merah ?? '-',
            'kontingen_merah' => $match->kontingen_merah ?? '-',
            'round' => $timerState['round'] ?? 1,
            'time_remaining' => $timerState['time_remaining'] ?? 120,
            'timer_status' => $timerState['status'] ?? 'stopped'
        ],
        'data' => [
            'skor_biru' => $score->skor_biru ?? 0,
            'skor_merah' => $score->skor_merah ?? 0,
            'binaan_biru' => $score->binaan_biru ?? 0,
            'binaan_merah' => $score->binaan_merah ?? 0,
            'teguran_biru' => $score->teguran_biru ?? 0,
            'teguran_merah' => $score->teguran_merah ?? 0,
            'peringatan_biru' => $score->peringatan_biru ?? 0,
            'peringatan_merah' => $score->peringatan_merah ?? 0,
            'jatuhan_biru' => $score->jatuhan_biru ?? 0,
            'jatuhan_merah' => $score->jatuhan_merah ?? 0,
        ]
    ]);
})->name('operator.monitor-display.data');

// TIMER
Route::middleware(['role:4'])->group(function () {
    Route::get('/timer', [TimerController::class, 'index'])->name('timer.dashboard');
    Route::post('/timer/sync', [TimerController::class, 'sync'])->name('timer.sync');
    Route::get('/timer/state', [TimerController::class, 'getState'])->name('timer.state');
});

// KETUA PERTANDINGAN
use App\Http\Controllers\Ketua\DashboardController as KetuaDashboardController;
use App\Http\Controllers\Ketua\MonitorController;
Route::middleware(['role:2'])->group(function () {
    Route::get('/ketua/dashboard', [KetuaDashboardController::class, 'index'])->name('ketua.dashboard');
    Route::get('/ketua/monitor', [MonitorController::class, 'index'])->name('ketua.monitor');
    Route::get('/ketua/monitor/data', [MonitorController::class, 'data'])->name('ketua.monitor.data');
});

// DEWAN
use App\Http\Controllers\Dewan\DashboardController as DewanDashboardController;
use App\Http\Controllers\Dewan\PenilaianAtletController;
use App\Http\Controllers\Dewan\PetugasPertandinganController;
Route::middleware(['role:3'])->group(function () {
    Route::get('/dewan/dashboard', [DewanDashboardController::class, 'index'])->name('dewan.dashboard');
    Route::get('/dewan/penilaian-atlet', [PenilaianAtletController::class, 'index'])->name('dewan.penilaian');
    Route::get('/dewan/penilaian-atlet/data', [PenilaianAtletController::class, 'getData'])->name('dewan.penilaian.data');
    Route::post('/dewan/penilaian-atlet/jatuhan', [PenilaianAtletController::class, 'addJatuhan'])->name('dewan.penilaian.jatuhan');
    Route::post('/dewan/penilaian-atlet/del-jatuhan', [PenilaianAtletController::class, 'delJatuhan'])->name('dewan.penilaian.del-jatuhan');
    Route::post('/dewan/penilaian-atlet/binaan', [PenilaianAtletController::class, 'addBinaan'])->name('dewan.penilaian.binaan');
    Route::post('/dewan/penilaian-atlet/teguran', [PenilaianAtletController::class, 'addTeguran'])->name('dewan.penilaian.teguran');
    Route::post('/dewan/penilaian-atlet/peringatan', [PenilaianAtletController::class, 'addPeringatan'])->name('dewan.penilaian.peringatan');
    Route::post('/dewan/penilaian-atlet/del-binaan', [PenilaianAtletController::class, 'delBinaan'])->name('dewan.penilaian.delBinaan');
    Route::post('/dewan/penilaian-atlet/del-teguran', [PenilaianAtletController::class, 'delTeguran'])->name('dewan.penilaian.delTeguran');
    Route::post('/dewan/penilaian-atlet/del-peringatan', [PenilaianAtletController::class, 'delPeringatan'])->name('dewan.penilaian.delPeringatan');
    Route::get('/dewan/petugas', [PetugasPertandinganController::class, 'index'])->name('dewan.petugas');
    Route::get('/dewan/petugas/add', [PetugasPertandinganController::class, 'add'])->name('dewan.petugas.add');
    Route::post('/dewan/petugas/store', [PetugasPertandinganController::class, 'store'])->name('dewan.petugas.store');
    Route::post('/dewan/petugas/{id}/run', [PetugasPertandinganController::class, 'runPetugas'])->name('dewan.petugas.run');
});

// JURI
use App\Http\Controllers\JuriController;
Route::middleware(['role:5'])->group(function () {
    Route::get('/juri1', [JuriController::class, 'index'])->name('juri1');
    Route::get('/juri2', [JuriController::class, 'index'])->name('juri2');
    Route::get('/juri3', [JuriController::class, 'index'])->name('juri3');

    Route::prefix('juri')->name('juri.')->group(function () {
        Route::post('/input-score', [JuriController::class, 'inputScore'])->name('input-score');
        Route::post('/delete-score', [JuriController::class, 'deleteScore'])->name('delete-score');
        Route::get('/history', [JuriController::class, 'getHistory'])->name('history');
    });
});

Route::prefix('operator')->name('operator.')->middleware(['role:1'])->group(function () {

    Route::prefix('tanding')->name('tanding.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');

        // INDEX — list waiting/finished/final
        Route::get('/', [TandingController::class, 'index'])
            ->name('index');

        // INPUT JADWAL
        Route::get('/jadwal',  [TandingController::class, 'addJadwal'])
            ->name('add-jadwal');

        Route::post('/jadwal', [TandingController::class, 'doAddJadwal'])
            ->name('do-create');                // ← satu-satunya nama untuk POST jadwal

        // EDIT JADWAL
        Route::get('/jadwal/{id}/edit',    [TandingController::class, 'editJadwal'])
            ->name('edit-jadwal');

        Route::put('/jadwal/{id}/update',  [TandingController::class, 'doEditJadwal'])
            ->name('do-edit-jadwal');

        // HAPUS JADWAL
        Route::delete('/jadwal/{id}',      [TandingController::class, 'deleteJadwal'])
            ->name('delete-jadwal');

        // FINALISASI
        Route::post('/{id}/finalisasi', [PertandinganController::class, 'finalisasi'])
            ->name('finalisasi.store');

        // INPUT PETUGAS
        Route::get('/petugas', [TandingController::class, 'addPetugas'])
            ->name('add-petugas');

        // WAITING LIST
        Route::prefix('waiting-list')->name('waiting-list.')->group(function () {

            Route::get('/',                [WaitingListController::class, 'index'])
                ->name('index');

            Route::get('/{id}/detail',    [WaitingListController::class, 'detail'])
                ->name('detail');

            Route::get('/{id}/edit',      [WaitingListController::class, 'update'])
                ->name('edit');

            Route::get('/{id}/edit-data', [WaitingListController::class, 'edit'])
                ->name('edit-data');

            Route::put('/{id}/update',    [WaitingListController::class, 'doUpdate'])
                ->name('update');

            Route::patch('/{id}/status',  [WaitingListController::class, 'doUpdateStatus'])
                ->name('update-status');

            Route::delete('/{id}/delete', [WaitingListController::class, 'doDelete'])
                ->name('delete');
        });

        // FINISHED
        Route::prefix('finished')->name('finished.')->group(function () {
            Route::get('/{id}/detail', function ($id) {
                return view('Operator.finished.detail', compact('id'));
            })->name('detail');
        });
    });

    Route::prefix('pertandingan')->name('pertandingan.')->group(function () {
        Route::get('/{id}/play', [PertandinganController::class, 'play'])->name('play');
    });

    Route::get('/monitor-display', function () {
        $match = \Illuminate\Support\Facades\DB::table('pertandingan')
            ->where('status', 'playing')->whereNull('deleted_at')->first();
        return view('Operator.monitor-display.scoreboard', compact('match'));
    })->name('monitor-display');

    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\PetugasController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\Operator\PetugasController::class, 'index'])->name('data');
        Route::get('/add', [\App\Http\Controllers\Operator\PetugasController::class, 'addPetugas'])->name('add');
        Route::post('/store', [\App\Http\Controllers\Operator\PetugasController::class, 'storePetugas'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Operator\PetugasController::class, 'editPetugas'])->name('edit');
        Route::put('/{id}/update', [\App\Http\Controllers\Operator\PetugasController::class, 'doEditPetugas'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Operator\PetugasController::class, 'deletePetugas'])->name('delete');
    });
});
