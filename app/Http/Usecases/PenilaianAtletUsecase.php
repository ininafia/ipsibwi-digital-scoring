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
        $timerState = \Illuminate\Support\Facades\Cache::get('current_timer_state_' . $id_pertandingan, ['status' => 'stopped']);
        if ($timerState['status'] !== 'playing') {
            throw new Exception('Waktu pertandingan sedang berhenti (Timer pause/stop)');
        }
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
                ->first();

            if ($skorRecord) {
                $currentTeguran = $skorRecord->{$teguranField};
                $currentBinaan = $skorRecord->{'binaan_' . $sudut};

                if ($currentBinaan < 2) {
                    return Response::buildErrorService("Harus menyelesaikan Binaan (2x) terlebih dahulu sebelum memberikan Teguran untuk sudut {$sudut}");
                }

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
                return Response::buildErrorService("Harus menyelesaikan Binaan (2x) terlebih dahulu sebelum memberikan Teguran untuk sudut {$sudut}");
            }

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
                ->first();

            if ($skorRecord) {
                $currentPeringatan = $skorRecord->{$peringatanField};
                $currentTeguran = $skorRecord->{'teguran_' . $sudut};

                if ($currentTeguran < 2) {
                    return Response::buildErrorService("Harus menyelesaikan Teguran (2x) terlebih dahulu sebelum memberikan Peringatan untuk sudut {$sudut}");
                }

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
                return Response::buildErrorService("Harus menyelesaikan Teguran (2x) terlebih dahulu sebelum memberikan Peringatan untuk sudut {$sudut}");
            }

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

    public function delHukuman(int $id_pertandingan, string $sudut): array
    {
        $funcName = $this->className . ".delHukuman";

        if (!in_array($sudut, ['biru', 'merah'])) {
            return Response::buildErrorService('Sudut tidak valid');
        }

        DB::beginTransaction();

        try {
            $this->validateTimerState($id_pertandingan);

            $binaanField = 'binaan_' . $sudut;
            $teguranField = 'teguran_' . $sudut;
            $peringatanField = 'peringatan_' . $sudut;
            $skorField = 'skor_' . $sudut;
            
            $skorRecord = DB::table('skor_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->first();

            if ($skorRecord) {
                $currentBinaan = $skorRecord->{$binaanField};
                $currentTeguran = $skorRecord->{$teguranField};
                $currentPeringatan = $skorRecord->{$peringatanField};

                $refund = 0;
                $updateData = ['updated_at' => now()];

                if ($currentPeringatan == 2) {
                    $refund = 10;
                    $updateData[$peringatanField] = 1;
                } else if ($currentPeringatan == 1) {
                    $refund = 5;
                    $updateData[$peringatanField] = 0;
                } else if ($currentTeguran == 2) {
                    $refund = 2;
                    $updateData[$teguranField] = 1;
                } else if ($currentTeguran == 1) {
                    $refund = 1;
                    $updateData[$teguranField] = 0;
                } else if ($currentBinaan == 2) {
                    $refund = 0;
                    $updateData[$binaanField] = 1;
                } else if ($currentBinaan == 1) {
                    $refund = 0;
                    $updateData[$binaanField] = 0;
                } else {
                    return Response::buildErrorService("Tidak ada hukuman untuk dihapus pada sudut {$sudut}");
                }

                $updateData[$skorField] = DB::raw($skorField . ' + ' . $refund);

                DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $id_pertandingan)
                    ->update($updateData);
            } else {
                return Response::buildErrorService("Belum ada skor tercatat untuk pertandingan ini");
            }

            DB::commit();
            return Response::buildSuccess(
                message: "Hukuman untuk sudut {$sudut} berhasil dihapus"
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

            // Hitung logika tampilan teks hukuman secara akumulatif
            $hukumanBiruText = '';
            if ($data['teguran_biru'] >= 1) $hukumanBiruText .= '-1';
            if ($data['teguran_biru'] >= 2) $hukumanBiruText .= '-2';
            if ($data['peringatan_biru'] >= 1) $hukumanBiruText .= '-5';
            if ($data['peringatan_biru'] >= 2) $hukumanBiruText .= '-10';

            $hukumanMerahText = '';
            if ($data['teguran_merah'] >= 1) $hukumanMerahText .= '-1';
            if ($data['teguran_merah'] >= 2) $hukumanMerahText .= '-2';
            if ($data['peringatan_merah'] >= 1) $hukumanMerahText .= '-5';
            if ($data['peringatan_merah'] >= 2) $hukumanMerahText .= '-10';

            // Hitung logika tampilan teks jatuhan (contoh: 3+3+3)
            $jatuhanBiruText = '';
            if ($data['jatuhan_biru'] > 0) {
                $jatuhanBiruText = implode('+', array_fill(0, $data['jatuhan_biru'], '3'));
            }

            $jatuhanMerahText = '';
            if ($data['jatuhan_merah'] > 0) {
                $jatuhanMerahText = implode('+', array_fill(0, $data['jatuhan_merah'], '3'));
            }

            $response = [
                'match' => [
                    'partai' => $match->partai ?? '-',
                    'sudut_biru' => $match->sudut_biru ?? '-',
                    'kontingen_biru' => $match->kontingen_biru ?? '-',
                    'sudut_merah' => $match->sudut_merah ?? '-',
                    'kontingen_merah' => $match->kontingen_merah ?? '-',
                    'round' => $timerState['round'] ?? 1,
                ],
                'data' => $data,
                'display' => [
                    'hukuman_biru_text' => $hukumanBiruText,
                    'hukuman_merah_text' => $hukumanMerahText,
                    'jatuhan_biru_text' => $jatuhanBiruText,
                    'jatuhan_merah_text' => $jatuhanMerahText,
                ]
            ];

            return Response::buildSuccess($response);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Response::buildErrorService($e->getMessage());
        }
    }
}
