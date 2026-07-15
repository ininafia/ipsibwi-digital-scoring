<?php

use Illuminate\Support\Facades\Route;

// ==========================================
// CONTROLLERS
// ==========================================
// Auth
use App\Http\Controllers\Operator\AuthController;

// Operator
use App\Http\Controllers\Operator\DashboardController;
use App\Http\Controllers\Operator\MonitorDisplayController;
use App\Http\Controllers\Operator\PertandinganController;
use App\Http\Controllers\Operator\PetugasController;
use App\Http\Controllers\Operator\TandingController;
use App\Http\Controllers\Operator\WaitingListController;

// Roles
use App\Http\Controllers\TimerController;
use App\Http\Controllers\Ketua\DashboardController as KetuaDashboardController;
use App\Http\Controllers\Ketua\MonitorController as KetuaMonitorController;
use App\Http\Controllers\Dewan\DashboardController as DewanDashboardController;
use App\Http\Controllers\Dewan\PenilaianAtletController as DewanPenilaianAtletController;
use App\Http\Controllers\Dewan\PetugasPertandinganController as DewanPetugasController;
use App\Http\Controllers\JuriController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', fn() => view('Operator.Landingpage'));
Route::get('/operator', fn() => redirect('/'));

// Authentication
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'doLogin')->name('login.process');
    Route::get('/logout', 'doLogout')->name('logout');

    // Specific Login Views
    Route::prefix('login')->group(function () {
        Route::get('/timer', 'login')->name('timer.login.view');
        Route::get('/juri', 'login')->name('juri.login.view');
        Route::get('/ketua', 'login')->name('ketua.login.view');
        Route::get('/dewan', 'login')->name('dewan.login.view');
    });
});

// Dashboard (Global)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Monitor Display Data (Global AJAX)
Route::get('/operator/monitor-display/data', [MonitorDisplayController::class, 'getData'])->name('operator.monitor-display.data');


// ==========================================
// ROLE 4: TIMER
// ==========================================
Route::middleware(['role:4'])->prefix('timer')->name('timer.')->controller(TimerController::class)->group(function () {
    Route::get('/', 'index')->name('dashboard');
    Route::post('/sync', 'sync')->name('sync');
    Route::get('/state', 'getState')->name('state');
});


// ==========================================
// ROLE 2: KETUA PERTANDINGAN
// ==========================================
Route::middleware(['role:2'])->prefix('ketua')->name('ketua.')->group(function () {
    Route::get('/dashboard', [KetuaDashboardController::class, 'index'])->name('dashboard');
    
    Route::controller(KetuaMonitorController::class)->prefix('monitor')->group(function () {
        Route::get('/', 'index')->name('monitor');
        Route::get('/data', 'data')->name('monitor.data');
    });

    Route::controller(\App\Http\Controllers\Ketua\AkurasiJuriController::class)->prefix('persentase-juri')->group(function () {
        Route::get('/', 'index')->name('akurasi');
        Route::get('/export-all', 'exportPdfAll')->name('akurasi.export.all');
        Route::get('/export/{id}', 'exportPdfMatch')->name('akurasi.export.match');
    });
});


// ==========================================
// ROLE 3: DEWAN
// ==========================================
Route::middleware(['role:3'])->prefix('dewan')->name('dewan.')->group(function () {
    Route::get('/dashboard', [DewanDashboardController::class, 'index'])->name('dashboard');
    
    Route::controller(DewanPenilaianAtletController::class)->prefix('penilaian-atlet')->group(function () {
        Route::get('/', 'index')->name('penilaian');
        Route::get('/data', 'getData')->name('penilaian.data');
        Route::post('/jatuhan', 'addJatuhan')->name('penilaian.jatuhan');
        Route::post('/del-jatuhan', 'delJatuhan')->name('penilaian.del-jatuhan');
        Route::post('/binaan', 'addBinaan')->name('penilaian.binaan');
        Route::post('/teguran', 'addTeguran')->name('penilaian.teguran');
        Route::post('/peringatan', 'addPeringatan')->name('penilaian.peringatan');
        Route::post('/del-binaan', 'delBinaan')->name('penilaian.delBinaan');
        Route::post('/del-teguran', 'delTeguran')->name('penilaian.delTeguran');
        Route::post('/del-peringatan', 'delPeringatan')->name('penilaian.delPeringatan');
    });

    Route::controller(DewanPetugasController::class)->prefix('petugas')->group(function () {
        Route::get('/', 'index')->name('petugas');
        Route::get('/add', 'add')->name('petugas.add');
        Route::post('/store', 'store')->name('petugas.store');
        Route::post('/{id}/run', 'runPetugas')->name('petugas.run');
        Route::delete('/{id}', 'destroy')->name('petugas.delete');
    });
});


// ==========================================
// ROLE 5: JURI
// ==========================================
Route::middleware(['role:5'])->group(function () {
    Route::controller(JuriController::class)->group(function () {
        Route::get('/juri1', 'index')->name('juri1');
        Route::get('/juri2', 'index')->name('juri2');
        Route::get('/juri3', 'index')->name('juri3');

        Route::prefix('juri')->name('juri.')->group(function () {
            Route::post('/input-score', 'inputScore')->name('input-score');
            Route::post('/delete-score', 'deleteScore')->name('delete-score');
            Route::get('/history', 'getHistory')->name('history');
        });
    });
});


// ==========================================
// ROLE 1: OPERATOR
// ==========================================
Route::prefix('operator')->name('operator.')->middleware(['role:1'])->group(function () {

    // Monitor Display Route
    Route::get('/monitor-display', [MonitorDisplayController::class, 'index'])->name('monitor-display');

    // Tanding Routes
    Route::prefix('tanding')->name('tanding.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::controller(TandingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            
            Route::get('/jadwal', 'addJadwal')->name('add-jadwal');
            Route::post('/jadwal', 'doAddJadwal')->name('do-create');
            Route::get('/jadwal/{id}/edit', 'editJadwal')->name('edit-jadwal');
            Route::put('/jadwal/{id}/update', 'doEditJadwal')->name('do-edit-jadwal');
            Route::delete('/jadwal/{id}', 'deleteJadwal')->name('delete-jadwal');

            Route::get('/petugas', 'addPetugas')->name('add-petugas');
        });

        Route::post('/{id}/finalisasi', [PertandinganController::class, 'finalisasi'])->name('finalisasi.store');

        Route::prefix('waiting-list')->name('waiting-list.')->controller(WaitingListController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}/detail', 'detail')->name('detail');
            Route::get('/{id}/edit', 'update')->name('edit');
            Route::get('/{id}/edit-data', 'edit')->name('edit-data');
            Route::put('/{id}/update', 'doUpdate')->name('update');
            Route::patch('/{id}/status', 'doUpdateStatus')->name('update-status');
            Route::delete('/{id}/delete', 'doDelete')->name('delete');
        });

        Route::prefix('finished')->name('finished.')->group(function () {
            Route::get('/{id}/detail', [\App\Http\Controllers\Operator\FinishedController::class, 'detail'])->name('detail');
            Route::get('/{id}/export-pdf', [\App\Http\Controllers\Operator\FinishedController::class, 'exportPdf'])->name('export-pdf');
        });
    });

    // Pertandingan Route
    Route::prefix('pertandingan')->name('pertandingan.')->controller(PertandinganController::class)->group(function () {
        Route::get('/{id}/play', 'play')->name('play');
    });

    // Petugas Route
    Route::prefix('petugas')->name('petugas.')->controller(PetugasController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'index')->name('data');
        Route::get('/add', 'addPetugas')->name('add');
        Route::post('/store', 'storePetugas')->name('store');
        Route::get('/{id}/edit', 'editPetugas')->name('edit');
        Route::put('/{id}/update', 'doEditPetugas')->name('update');
        Route::delete('/{id}', 'deletePetugas')->name('delete');
    });
});
