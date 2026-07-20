<?php

namespace App\Http\Usecases;

use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianAtletUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "PenilaianAtletUsecase";
    }

    private function validateTimerState(int $id_pertandingan): void
    {
        // Validasi timer dimatikan sesuai permintaan agar Dewan bisa input kapan saja
        // $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $id_pertandingan, ['status' => 'stopped']);
        // if ($timerState['status'] !== 'playing') {
        //     throw new Exception('Waktu pertandingan sedang berhenti (Timer pause/stop)');
        // }
    }

    private function logRiwayatHukuman(int $id_pertandingan, string $sudut, string $jenis, string $action): void
    {
        $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $id_pertandingan, ['round' => 1]);
        $idBabak = $timerState['round'] ?? 1;
        
        DB::table('riwayat_hukuman')->insert([
            'id_pertandingan' => $id_pertandingan,
            'id_babak'        => $idBabak,
            'sudut'           => $sudut,
            'jenis_hukuman'   => $jenis,
            'action'          => $action,
            'created_by'      => session('user_id'),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function addJatuhan(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".addJatuhan";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $skorField = 'skor_' . $sudut;
            $jatuhanField = 'jatuhan_' . $sudut;
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $id_pertandingan)
                    ->update([
                        $skorField => DB::raw($skorField . ' + 3'),
                        $jatuhanField => DB::raw($jatuhanField . ' + 1'),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('skor_pertandingan')->insert([
                    'id_pertandingan' => $id_pertandingan,
                    'skor_biru' => $sudut === 'biru' ? 3 : 0,
                    'skor_merah' => $sudut === 'merah' ? 3 : 0,
                    'binaan_biru' => 0,
                    'binaan_merah' => 0,
                    'jatuhan_biru' => $sudut === 'biru' ? 1 : 0,
                    'jatuhan_merah' => $sudut === 'merah' ? 1 : 0,
                    'updated_at' => now(),
                ]);
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'jatuhan', 'add');
            DB::commit();
            return Response::buildSuccess(
                message: "Jatuhan berhasil ditambahkan"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delJatuhan(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".delJatuhan";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $skorField = 'skor_' . $sudut;
            $jatuhanField = 'jatuhan_' . $sudut;
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                if ($skorRecord->{$jatuhanField} > 0) {
                    DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $id_pertandingan)
                        ->update([
                            $skorField => DB::raw($skorField . ' - 3'),
                            $jatuhanField => DB::raw($jatuhanField . ' - 1'),
                            'updated_at' => now(),
                        ]);
                } else {
                    return Response::buildErrorService("Tidak ada jatuhan yang bisa dihapus untuk sudut {$sudut}");
                }
            } else {
                return Response::buildErrorService("Belum ada skor tercatat untuk pertandingan ini");
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'jatuhan', 'delete');
            DB::commit();
            return Response::buildSuccess(
                message: "Jatuhan berhasil dihapus"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function addBinaan(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".addBinaan";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $binaanField = 'binaan_' . $sudut;
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                $currentBinaan = $skorRecord->{$binaanField};
                if ($currentBinaan >= 2) {
                    return Response::buildErrorService("Binaan maksimal 2 kali untuk sudut {$sudut}");
                }
                
                DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $id_pertandingan)
                    ->update([
                        $binaanField => $currentBinaan + 1,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('skor_pertandingan')->insert([
                    'id_pertandingan' => $id_pertandingan,
                    'skor_biru' => 0,
                    'skor_merah' => 0,
                    'binaan_biru' => $sudut === 'biru' ? 1 : 0,
                    'binaan_merah' => $sudut === 'merah' ? 1 : 0,
                    'updated_at' => now(),
                ]);
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'binaan', 'add');
            DB::commit();
            return Response::buildSuccess(
                message: "Binaan berhasil ditambahkan"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function addTeguran(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".addTeguran";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $teguranField = 'teguran_' . $sudut;
            $skorField = 'skor_' . $sudut;
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                $currentTeguran = $skorRecord->{$teguranField};

                if ($currentTeguran >= 2) {
                    return Response::buildErrorService("Teguran maksimal 2 kali untuk sudut {$sudut}");
                }

                $pointsToDeduct = ($currentTeguran == 0) ? 1 : 2;
                
                DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $id_pertandingan)
                    ->update([
                        $teguranField => $currentTeguran + 1,
                        $skorField => DB::raw($skorField . ' - ' . $pointsToDeduct),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('skor_pertandingan')->insert([
                    'id_pertandingan' => $id_pertandingan,
                    'skor_biru' => $sudut === 'biru' ? -1 : 0,
                    'skor_merah' => $sudut === 'merah' ? -1 : 0,
                    'binaan_biru' => 0,
                    'binaan_merah' => 0,
                    'teguran_biru' => $sudut === 'biru' ? 1 : 0,
                    'teguran_merah' => $sudut === 'merah' ? 1 : 0,
                    'jatuhan_biru' => 0,
                    'jatuhan_merah' => 0,
                    'updated_at' => now(),
                ]);
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'teguran', 'add');
            DB::commit();
            return Response::buildSuccess(
                message: "Teguran berhasil ditambahkan"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function addPeringatan(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".addPeringatan";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $peringatanField = 'peringatan_' . $sudut;
            $skorField = 'skor_' . $sudut;
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                $currentPeringatan = $skorRecord->{$peringatanField};

                if ($currentPeringatan >= 2) {
                    return Response::buildErrorService("Peringatan maksimal 2 kali untuk sudut {$sudut}");
                }

                $pointsToDeduct = ($currentPeringatan == 0) ? 5 : 10;
                
                DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $id_pertandingan)
                    ->update([
                        $peringatanField => $currentPeringatan + 1,
                        $skorField => DB::raw($skorField . ' - ' . $pointsToDeduct),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('skor_pertandingan')->insert([
                    'id_pertandingan' => $id_pertandingan,
                    'skor_biru' => $sudut === 'biru' ? -5 : 0,
                    'skor_merah' => $sudut === 'merah' ? -5 : 0,
                    'binaan_biru' => 0,
                    'binaan_merah' => 0,
                    'teguran_biru' => 0,
                    'teguran_merah' => 0,
                    'peringatan_biru' => $sudut === 'biru' ? 1 : 0,
                    'peringatan_merah' => $sudut === 'merah' ? 1 : 0,
                    'jatuhan_biru' => 0,
                    'jatuhan_merah' => 0,
                    'updated_at' => now(),
                ]);
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'peringatan', 'add');
            DB::commit();
            return Response::buildSuccess(
                message: "Peringatan berhasil ditambahkan"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delBinaan(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".delBinaan";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $binaanField = 'binaan_' . $sudut;
            $teguranField = 'teguran_' . $sudut;
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                if ($skorRecord->{$binaanField} > 0) {
                    DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $id_pertandingan)
                        ->update([
                            $binaanField => DB::raw($binaanField . ' - 1'),
                            'updated_at' => now(),
                        ]);
                } else {
                    return Response::buildErrorService("Tidak ada Binaan yang bisa dihapus untuk sudut {$sudut}");
                }
            } else {
                return Response::buildErrorService("Belum ada skor tercatat untuk pertandingan ini");
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'binaan', 'delete');
            DB::commit();
            return Response::buildSuccess(
                message: "Binaan berhasil dihapus"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delTeguran(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".delTeguran";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $teguranField = 'teguran_' . $sudut;
            $peringatanField = 'peringatan_' . $sudut;
            $skorField = 'skor_' . $sudut;
            
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                $currentTeguran = $skorRecord->{$teguranField};
                if ($currentTeguran > 0) {
                    $refund = ($currentTeguran == 2) ? 2 : 1;
                    DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $id_pertandingan)
                        ->update([
                            $teguranField => DB::raw($teguranField . ' - 1'),
                            $skorField => DB::raw($skorField . ' + ' . $refund),
                            'updated_at' => now(),
                        ]);
                } else {
                    return Response::buildErrorService("Tidak ada Teguran yang bisa dihapus untuk sudut {$sudut}");
                }
            } else {
                return Response::buildErrorService("Belum ada skor tercatat untuk pertandingan ini");
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'teguran', 'delete');
            DB::commit();
            return Response::buildSuccess(
                message: "Teguran berhasil dihapus"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delPeringatan(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".delPeringatan";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $peringatanField = 'peringatan_' . $sudut;
            $skorField = 'skor_' . $sudut;
            
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->lockForUpdate()  // BUG-2 FIX: cegah double-click
                ->first();

            if ($skorRecord) {
                $currentPeringatan = $skorRecord->{$peringatanField};
                if ($currentPeringatan > 0) {
                    $refund = ($currentPeringatan == 2) ? 10 : 5;
                    DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $id_pertandingan)
                        ->update([
                            $peringatanField => DB::raw($peringatanField . ' - 1'),
                            $skorField => DB::raw($skorField . ' + ' . $refund),
                            'updated_at' => now(),
                        ]);
                } else {
                    return Response::buildErrorService("Tidak ada Peringatan yang bisa dihapus untuk sudut {$sudut}");
                }
            } else {
                return Response::buildErrorService("Belum ada skor tercatat untuk pertandingan ini");
            }

                        $this->logRiwayatHukuman($id_pertandingan, $sudut, 'peringatan', 'delete');
            DB::commit();
            return Response::buildSuccess(
                message: "Peringatan berhasil dihapus"
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getData(): array
    {
        try {
            $match = DB::table('pertandingan')
                ->where('status', 'playing')
                ->whereNull('deleted_at')
                ->first();

            if (!$match) {
                return Response::buildErrorService('Tidak ada pertandingan aktif');
            }

            $score = DB::table('skor_pertandingan')->where('id_pertandingan', $match->id)->first();
            
            $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $match->id, [
                'round' => 1,
                'time_remaining' => 120,
                'status' => 'stopped'
            ]);

            $data = [
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
            ];

            // Ambil data nama dewan (id_role = 3)
            $dewanName = 'MENUNGGU PENUGASAN';
            $dewanAssignment = DB::table('petugas_pertandingan')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('petugas_pertandingan.id_pertandingan', $match->id)
                ->where('petugas_pertandingan.id_role', 3)
                ->first(['data_petugas.nama']);
                
            if ($dewanAssignment) {
                $dewanName = strtoupper($dewanAssignment->nama);
            }

            // Ambil riwayat hukuman untuk menampilkan per ronde
            $riwayatHukuman = DB::table('riwayat_hukuman')
                ->where('id_pertandingan', $match->id)
                ->get();
            
            $penaltiesPerRound = [];
            for ($r = 1; $r <= 3; $r++) {
                $penaltiesPerRound[$r] = [
                    'jatuhan_biru' => 0,
                    'jatuhan_merah' => 0,
                    'binaan_biru' => 0,
                    'binaan_merah' => 0,
                    'teguran_biru' => 0,
                    'teguran_merah' => 0,
                    'peringatan_biru' => 0,
                    'peringatan_merah' => 0,
                ];
            }

            foreach ($riwayatHukuman as $rh) {
                $r = $rh->id_babak;
                if ($r >= 1 && $r <= 3) {
                    $field = $rh->jenis_hukuman . '_' . $rh->sudut;
                    if (isset($penaltiesPerRound[$r][$field])) {
                        $val = ($rh->action === 'add') ? 1 : -1;
                        $penaltiesPerRound[$r][$field] += $val;
                    }
                }
            }

            // Format text untuk UI Dewan (seperti di Monitor Ketua):
            $penaltiesFormatted = [];
            for ($r = 1; $r <= 3; $r++) {
                $p = $penaltiesPerRound[$r];
                
                $hukumanBiru = '';
                if ($p['teguran_biru'] >= 1) $hukumanBiru .= '-1';
                if ($p['teguran_biru'] >= 2) $hukumanBiru .= '-2';
                if ($p['peringatan_biru'] >= 1) $hukumanBiru .= '-5';
                if ($p['peringatan_biru'] >= 2) $hukumanBiru .= '-10';

                $hukumanMerah = '';
                if ($p['teguran_merah'] >= 1) $hukumanMerah .= '-1';
                if ($p['teguran_merah'] >= 2) $hukumanMerah .= '-2';
                if ($p['peringatan_merah'] >= 1) $hukumanMerah .= '-5';
                if ($p['peringatan_merah'] >= 2) $hukumanMerah .= '-10';

                $jatuhanBiru = '';
                if ($p['jatuhan_biru'] > 0) {
                    $jatuhanBiru = implode('+', array_fill(0, $p['jatuhan_biru'], '3'));
                }
                
                $jatuhanMerah = '';
                if ($p['jatuhan_merah'] > 0) {
                    $jatuhanMerah = implode('+', array_fill(0, $p['jatuhan_merah'], '3'));
                }

                $penaltiesFormatted[$r] = [
                    'jatuhan_biru' => $jatuhanBiru,
                    'jatuhan_merah' => $jatuhanMerah,
                    'binaan_biru' => $p['binaan_biru'] > 0 ? $p['binaan_biru'] : '',
                    'binaan_merah' => $p['binaan_merah'] > 0 ? $p['binaan_merah'] : '',
                    'hukuman_biru' => $hukumanBiru,
                    'hukuman_merah' => $hukumanMerah,
                ];
            }

            $response = [
                'match' => [
                    'id' => $match->id,
                    'partai' => $match->partai ?? '-',
                    'sudut_biru' => $match->sudut_biru ?? '-',
                    'kontingen_biru' => $match->kontingen_biru ?? '-',
                    'sudut_merah' => $match->sudut_merah ?? '-',
                    'kontingen_merah' => $match->kontingen_merah ?? '-',
                    'round' => $timerState['round'] ?? 1,
                    'timer_status' => $timerState['status'] ?? 'stopped',
                    'time_remaining' => $timerState['time_remaining'] ?? 0,
                ],
                'data' => $data,
                'penalties_formatted' => $penaltiesFormatted,
                'dewan' => [
                    'nama' => $dewanName,
                    'posisi' => 'DEWAN'
                ]
            ];

            return Response::buildSuccess($response);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Response::buildErrorService($e->getMessage());
        }
    }
}
