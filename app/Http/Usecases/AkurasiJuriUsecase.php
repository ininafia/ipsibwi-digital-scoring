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
                
                $persentase = 0;
                if ($total_input > 0) {
                    $persentase = round(($total_sah / $total_input) * 100, 2);
                }

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
}
