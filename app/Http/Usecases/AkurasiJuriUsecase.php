<?php

namespace App\Http\Usecases;

use App\Http\Presenter\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AkurasiJuriUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "AkurasiJuriUsecase";
    }

    /**
     * Hitung akurasi juri setelah pertandingan difinalisasi
     */
    public function calculateForMatch(int $id_pertandingan): array
    {
        $funcName = $this->className . ".calculateForMatch";

        try {
            // Ambil petugas juri (role = 5) untuk pertandingan ini
            $juris = DB::table('petugas_pertandingan')
                ->where('id_pertandingan', $id_pertandingan)
                ->where('id_role', 5)
                ->get();

            if ($juris->isEmpty()) {
                return Response::buildErrorService("Tidak ada juri yang ditugaskan pada pertandingan ini");
            }

            $results = [];

            foreach ($juris as $juri) {
                // Total input = jumlah input juri yang bukan expired (termasuk pending atau consumed)
                // Sebaiknya, status 'expired' berarti tidak mencapai konsensus/tidak sah, tapi tetap dihitung sebagai input.
                // Jadi total_input adalah total event yang diinput juri ini (kecuali kalau deleted).
                $total_input = DB::table('score_events')
                    ->where('match_id', $id_pertandingan)
                    ->where('judge_id', $juri->id)
                    ->count();

                // Total sah = jumlah vote (dukungan konsensus) dari juri ini
                $total_sah = DB::table('score_award_votes')
                    ->where('judge_id', $juri->id)
                    ->whereExists(function ($query) use ($id_pertandingan) {
                        $query->select(DB::raw(1))
                              ->from('score_awards')
                              ->whereColumn('score_awards.id', 'score_award_votes.award_id')
                              ->where('score_awards.match_id', $id_pertandingan);
                    })
                    ->count();

                $total_tidak_sah = max(0, $total_input - $total_sah);
                
                // Kalkulasi rata-rata per babak (3 babak)
                $akurasi_babak_total = 0;
                for ($roundNum = 1; $roundNum <= 3; $roundNum++) {
                    $input_babak = DB::table('score_events')
                        ->where('match_id', $id_pertandingan)
                        ->where('judge_id', $juri->id)
                        ->where('round', $roundNum)
                        ->count();

                    $sah_babak = DB::table('score_award_votes')
                        ->where('judge_id', $juri->id)
                        ->whereExists(function ($query) use ($id_pertandingan, $roundNum) {
                            $query->select(DB::raw(1))
                                  ->from('score_awards')
                                  ->whereColumn('score_awards.id', 'score_award_votes.award_id')
                                  ->where('score_awards.match_id', $id_pertandingan)
                                  ->where('score_awards.round', $roundNum);
                        })
                        ->count();

                    $ak_babak = $input_babak > 0 ? ($sah_babak / $input_babak) * 100 : 0;
                    $akurasi_babak_total += $ak_babak;
                }

                // Akurasi per partai = Rata-rata dari 3 babak
                $persentase = round($akurasi_babak_total / 3, 2);

                // Hapus data lama jika dire-finalisasi
                DB::table('akurasi_juri')
                    ->where('id_petugas_pertandingan', $juri->id)
                    ->where('id_pertandingan', $id_pertandingan)
                    ->delete();

                // Insert perhitungan akurasi
                DB::table('akurasi_juri')->insert([
                    'id_petugas_pertandingan' => $juri->id,
                    'id_pertandingan'         => $id_pertandingan,
                    'total_input'             => $total_input,
                    'total_nilai_sah'         => $total_sah,
                    'total_nilai_tidak_sah'   => $total_tidak_sah,
                    'persentase_akurasi'      => $persentase,
                    'tanggal_dihitung'        => now()
                ]);

                $results[] = [
                    'juri_id' => $juri->id,
                    'posisi'  => $juri->posisi,
                    'akurasi' => $persentase
                ];
            }

            return Response::buildSuccess(
                data: ['list' => $results],
                message: "Akurasi juri berhasil dihitung"
            );
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Ambil rekap akurasi juri untuk monitor ketua
     */
    public function getDataAkurasi(): array
    {
        $funcName = $this->className . ".getDataAkurasi";

        try {
            // Kita cari pertandingan terakhir yang difinalisasi atau pertandingan aktif
            $match = DB::table('pertandingan')
                ->whereNull('deleted_at')
                ->whereIn('status', ['playing', 'finished', 'final'])
                ->orderByRaw("FIELD(status, 'playing', 'finished', 'final')")
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$match) {
                return Response::buildErrorService("Belum ada data pertandingan aktif/selesai");
            }

            // Jika masih playing, mungkin kita bisa menghitung real-time (tapi tidak disimpan ke database)
            // Di instruksi "menampilkan data akurasi juri yang tersimpan", jadi kita ambil dari database 'akurasi_juri'
            
            $akurasi = DB::table('akurasi_juri')
                ->join('petugas_pertandingan', 'akurasi_juri.id_petugas_pertandingan', '=', 'petugas_pertandingan.id')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('akurasi_juri.id_pertandingan', $match->id)
                ->select(
                    'data_petugas.nama as nama_juri',
                    'petugas_pertandingan.posisi',
                    'akurasi_juri.total_input',
                    'akurasi_juri.total_nilai_sah',
                    'akurasi_juri.total_nilai_tidak_sah',
                    'akurasi_juri.persentase_akurasi'
                )
                ->get();

            return Response::buildSuccess(
                data: [
                    'match' => [
                        'id' => $match->id,
                        'partai' => $match->partai,
                        'status' => $match->status
                    ],
                    'akurasi' => collect($akurasi)->toArray()
                ],
                message: "Data akurasi berhasil dimuat"
            );

        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Ambil seluruh data akurasi juri dari tabel akurasi_juri
     */
    public function getAllAkurasi(): array
    {
        $funcName = $this->className . ".getAllAkurasi";

        try {
            $akurasiRecords = DB::table('akurasi_juri')
                ->join('petugas_pertandingan', 'akurasi_juri.id_petugas_pertandingan', '=', 'petugas_pertandingan.id')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->join('pertandingan', 'akurasi_juri.id_pertandingan', '=', 'pertandingan.id')
                ->select(
                    'pertandingan.id as match_id',
                    'pertandingan.partai',
                    'pertandingan.gelanggang',
                    'pertandingan.kelas',
                    'pertandingan.golongan',
                    'petugas_pertandingan.id as id_petugas_pertandingan',
                    'data_petugas.nama as nama_juri',
                    'petugas_pertandingan.posisi',
                    'akurasi_juri.total_input',
                    'akurasi_juri.total_nilai_sah',
                    'akurasi_juri.total_nilai_tidak_sah',
                    'akurasi_juri.persentase_akurasi',
                    'akurasi_juri.tanggal_dihitung'
                )
                ->orderBy('pertandingan.partai', 'asc')
                ->get();

            // Group by match_id
            $groupedByMatch = [];
            
            $total_all_akurasi = 0;
            $count_all_akurasi = 0;

            foreach ($akurasiRecords as $row) {
                if (!isset($groupedByMatch[$row->match_id])) {
                    $groupedByMatch[$row->match_id] = [
                        'match_id' => $row->match_id,
                        'partai' => $row->partai,
                        'gelanggang' => $row->gelanggang,
                        'kelas' => $row->kelas,
                        'golongan' => $row->golongan,
                        'tanggal_dihitung' => $row->tanggal_dihitung,
                        'juris' => []
                    ];
                }

                // Kalkulasi per babak
                $babakData = [];
                for ($roundNum = 1; $roundNum <= 3; $roundNum++) {
                    $total_input_babak = DB::table('score_events')
                        ->where('match_id', $row->match_id)
                        ->where('judge_id', $row->id_petugas_pertandingan)
                        ->where('round', $roundNum)
                        ->count();

                    $total_sah_babak = DB::table('score_award_votes')
                        ->where('judge_id', $row->id_petugas_pertandingan)
                        ->whereExists(function ($query) use ($row, $roundNum) {
                            $query->select(DB::raw(1))
                                ->from('score_awards')
                                ->whereColumn('score_awards.id', 'score_award_votes.award_id')
                                ->where('score_awards.match_id', $row->match_id)
                                ->where('score_awards.round', $roundNum);
                        })
                        ->count();

                    $total_tidak_sah_babak = max(0, $total_input_babak - $total_sah_babak);
                    $akurasi_babak = $total_input_babak > 0 ? round(($total_sah_babak / $total_input_babak) * 100, 1) : 0;

                    $babakData["babak_$roundNum"] = [
                        'input' => $total_input_babak,
                        'sah' => $total_sah_babak,
                        'tidak_sah' => $total_tidak_sah_babak,
                        'akurasi' => $akurasi_babak
                    ];
                }

                $groupedByMatch[$row->match_id]['juris'][] = [
                    'id_petugas' => $row->id_petugas_pertandingan,
                    'nama_juri' => $row->nama_juri,
                    'posisi' => $row->posisi,
                    'total_input' => $row->total_input,
                    'total_nilai_sah' => $row->total_nilai_sah,
                    'total_nilai_tidak_sah' => $row->total_nilai_tidak_sah,
                    'persentase_akurasi' => $row->persentase_akurasi,
                    'rounds' => $babakData
                ];
                
                $total_all_akurasi += $row->persentase_akurasi;
                $count_all_akurasi++;
            }

            $event_accuracy = $count_all_akurasi > 0 ? round($total_all_akurasi / $count_all_akurasi, 2) : 0;

            return Response::buildSuccess(
                data: [
                    'matches' => array_values($groupedByMatch),
                    'event_accuracy' => $event_accuracy
                ],
                message: "Data seluruh akurasi juri berhasil dimuat"
            );

        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }
}
