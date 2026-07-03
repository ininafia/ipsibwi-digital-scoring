<?php

namespace App\Http\Usecases;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Presenter\Response;
use App\Entities\ResponseEntity;
use Exception;

class JuriUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "JuriUsecase";
    }

    /**
     * Input ketukan juri
     */
    public function inputScore(Request $request): array
    {
        $funcName = $this->className . '.inputScore';
        DB::beginTransaction();

        try {
            $idPertandingan = $request->input('id_pertandingan');
            $idBabak = $request->input('id_babak');
            $idPetugas = $request->input('id_petugas_pertandingan');
            $sudut = $request->input('sudut'); // 'merah' atau 'biru'
            $idKategoriNilai = $request->input('id_kategori_nilai');
            $nilai = $request->input('nilai'); // 1 atau 2

            // Cari kategori nilai untuk mendapatkan delay_max (batas waktu)
            $kategori = DB::table('kategori_nilai')->where('id', $idKategoriNilai)->first();
            if (!$kategori) {
                return Response::buildErrorService('Kategori nilai tidak ditemukan');
            }

            $delayMax = (float) $kategori->delay_max;
            $currentTime = microtime(true);

            // Cari event aktif: event untuk pertandingan, babak, dan sudut yang sama
            // dimana statusnya masih pending dan belum melewati delayMax
            $activeEvents = DB::table('input_nilai_juri')
                ->select('event_id')
                ->selectRaw('MIN(waktu_input) as start_time')
                ->where('id_pertandingan', $idPertandingan)
                ->where('id_babak', $idBabak)
                ->where('sudut', $sudut)
                ->where('status', 'pending')
                ->groupBy('event_id')
                ->get();

            $targetEventId = null;

            foreach ($activeEvents as $event) {
                if (($currentTime - $event->start_time) <= $delayMax) {
                    $targetEventId = $event->event_id;
                    break;
                }
            }

            if (!$targetEventId) {
                // Buat event_id baru (mulai jendela waktu 3 detik)
                $targetEventId = Str::random(15);
            } else {
                // Cek apakah juri ini sudah menginput di event yang sama
                $alreadyInput = DB::table('input_nilai_juri')
                    ->where('event_id', $targetEventId)
                    ->where('id_petugas_pertandingan', $idPetugas)
                    ->exists();

                if ($alreadyInput) {
                    DB::rollBack();
                    return Response::buildSuccess(['message' => 'Sudah menginput di event ini (terabaikan)']);
                }
            }

            // Simpan input juri
            DB::table('input_nilai_juri')->insert([
                'event_id' => $targetEventId,
                'id_pertandingan' => $idPertandingan,
                'id_babak' => $idBabak,
                'id_petugas_pertandingan' => $idPetugas,
                'sudut' => $sudut,
                'id_kategori_nilai' => $idKategoriNilai,
                'nilai' => $nilai,
                'waktu_input' => $currentTime,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            DB::commit();

            // Cek apakah sudah terkumpul 3 input dari 3 juri.
            // Jika sudah 3 juri masuk, langsung putuskan (resolve) tanpa perlu menunggu waktu habis.
            $inputCount = DB::table('input_nilai_juri')->where('event_id', $targetEventId)->count();
            if ($inputCount >= 3) {
                $this->resolveEvent($targetEventId);
            }

            return Response::buildSuccess(['event_id' => $targetEventId], 200, 'Berhasil mencatat nilai');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Memutuskan status skor untuk suatu event (Sah / Tidak Sah)
     */
    public function resolveEvent(string $eventId): void
    {
        $funcName = $this->className . '.resolveEvent';
        DB::beginTransaction();

        try {
            // Cek kembali input untuk event ini (harus pending agar tidak diproses ganda)
            $inputs = DB::table('input_nilai_juri')
                ->where('event_id', $eventId)
                ->where('status', 'pending')
                ->get();

            if ($inputs->isEmpty()) {
                DB::rollBack();
                return; // Mungkin sudah di-resolve
            }

            $firstInput = $inputs->first();
            
            // Hitung distribusi nilai.
            // Rule 6: Apabila 2 juri memberikan nilai yang sama sementara 1 juri memberikan nilai berbeda
            // maka nilai yang sah yaitu nilai yang bernilai sama.
            $nilaiCounts = [];
            foreach ($inputs as $input) {
                if (!isset($nilaiCounts[$input->nilai])) {
                    $nilaiCounts[$input->nilai] = 0;
                }
                $nilaiCounts[$input->nilai]++;
            }

            $winningNilai = null;
            $maxVotes = 0;
            foreach ($nilaiCounts as $n => $count) {
                if ($count >= 2) {
                    $winningNilai = $n;
                    $maxVotes = $count;
                    break; // Rule 1: Minimal 2 dari 3 juri memberikan nilai yang sama
                }
            }

            if ($winningNilai !== null) {
                // KONSENSUS TERCAPAI (Ada >= 2 juri dengan nilai sama)
                
                // Ubah status input yang sama (menang) menjadi 'sah'
                DB::table('input_nilai_juri')
                    ->where('event_id', $eventId)
                    ->where('nilai', $winningNilai)
                    ->update(['status' => 'sah']);
                
                // Ubah status input yang berbeda (kalah) menjadi 'tidak_sah'
                DB::table('input_nilai_juri')
                    ->where('event_id', $eventId)
                    ->where('nilai', '!=', $winningNilai)
                    ->update(['status' => 'tidak_sah']);

                $winningInput = $inputs->where('nilai', $winningNilai)->first();

                // Simpan ke tabel hasil_penilaian
                DB::table('hasil_penilaian')->insert([
                    'event_id' => $eventId,
                    'id_pertandingan' => $winningInput->id_pertandingan,
                    'id_babak' => $winningInput->id_babak,
                    'sudut' => $winningInput->sudut,
                    'id_kategori_nilai' => $winningInput->id_kategori_nilai,
                    'nilai' => $winningInput->nilai,
                    'waktu_event' => $winningInput->waktu_input,
                    'jumlah_juri' => count($inputs),
                    'status' => 'valid',
                    'alasan' => 'Konsensus Tercapai (' . $maxVotes . ' suara)',
                    'created_at' => now(),
                ]);

                // Tambahkan poin ke tabel skor_pertandingan
                $skorField = 'skor_' . $winningInput->sudut;
                $skorRecord = DB::table('skor_pertandingan')
                    ->where('id_pertandingan', $winningInput->id_pertandingan)
                    ->first();

                if ($skorRecord) {
                    DB::table('skor_pertandingan')
                        ->where('id_pertandingan', $winningInput->id_pertandingan)
                        ->update([
                            $skorField => DB::raw($skorField . ' + ' . $winningInput->nilai),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('skor_pertandingan')->insert([
                        'id_pertandingan' => $winningInput->id_pertandingan,
                        $skorField => $winningInput->nilai,
                        'updated_at' => now(),
                    ]);
                }

            } else {
                // KONSENSUS TIDAK TERCAPAI 
                // (Hanya 1 juri yang menginput sampai waktu habis, atau 3 juri beda nilai yang tidak mungkin terjadi dalam 2 opsi nilai)
                // Rule 5: Nilai tetap masuk tetapi dinyatakan tidak sah atau tercoret
                DB::table('input_nilai_juri')
                    ->where('event_id', $eventId)
                    ->update(['status' => 'tidak_sah']);
                
                DB::table('hasil_penilaian')->insert([
                    'event_id' => $eventId,
                    'id_pertandingan' => $firstInput->id_pertandingan,
                    'id_babak' => $firstInput->id_babak,
                    'sudut' => $firstInput->sudut,
                    'id_kategori_nilai' => $firstInput->id_kategori_nilai,
                    'nilai' => 0,
                    'waktu_event' => $firstInput->waktu_input,
                    'jumlah_juri' => count($inputs),
                    'status' => 'rejected',
                    'alasan' => 'Tidak mencapai kuorum (Hanya ' . count($inputs) . ' suara)',
                    'created_at' => now(),
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
        }
    }

    /**
     * Memeriksa dan memutuskan event yang sudah melewati batas waktu (expired).
     * Dipanggil berkala melalui polling dari layar Dewan/Operator.
     */
    public function resolveExpiredEvents(): void
    {
        $funcName = $this->className . '.resolveExpiredEvents';
        try {
            $currentTime = microtime(true);
            
            // Ambil semua event_id yang masih pending
            $pendingEvents = DB::table('input_nilai_juri')
                ->select('event_id', 'id_kategori_nilai')
                ->selectRaw('MIN(waktu_input) as start_time')
                ->where('status', 'pending')
                ->groupBy('event_id', 'id_kategori_nilai')
                ->get();

            // Loop untuk cek mana yang sudah lebih dari delay_max (3 detik)
            foreach ($pendingEvents as $event) {
                $kategori = DB::table('kategori_nilai')->where('id', $event->id_kategori_nilai)->first();
                $delayMax = $kategori ? (float) $kategori->delay_max : 3.00;

                if (($currentTime - $event->start_time) > $delayMax) {
                    // Waktu sudah habis, tapi event masih 'pending', panggil resolusi.
                    // Jika kurang dari 2, akan dinyatakan tidak sah.
                    $this->resolveEvent($event->event_id);
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
        }
    }

    public function deleteScore(Request $request): array
    {
        $funcName = $this->className . '.deleteScore';
        DB::beginTransaction();

        try {
            $idPertandingan = $request->input('id_pertandingan');
            $idBabak = $request->input('id_babak');
            $idPetugas = $request->input('id_petugas_pertandingan');
            $sudut = $request->input('sudut');

            // Find the most recent pending input
            $lastInput = DB::table('input_nilai_juri')
                ->where('id_pertandingan', $idPertandingan)
                ->where('id_babak', $idBabak)
                ->where('id_petugas_pertandingan', $idPetugas)
                ->where('sudut', $sudut)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastInput) {
                DB::table('input_nilai_juri')->where('id', $lastInput->id)->delete();
                DB::commit();
                return Response::buildSuccess(null, 200, 'Berhasil menghapus nilai pending');
            }

            DB::rollBack();
            return Response::buildErrorService('Tidak ada nilai pending untuk dihapus');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getHistory(Request $request): array
    {
        $funcName = $this->className . '.getHistory';
        try {
            $idPertandingan = $request->input('id_pertandingan');
            $idPetugas = $request->input('id_petugas_pertandingan');

            $history = DB::table('input_nilai_juri')
                ->where('id_pertandingan', $idPertandingan)
                ->where('id_petugas_pertandingan', $idPetugas)
                // We return all inputs (pending or sah) to display in the UI history
                ->orderBy('waktu_input', 'asc')
                ->get();

            return Response::buildSuccess($history, 200, 'Berhasil mengambil riwayat');
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }
}
