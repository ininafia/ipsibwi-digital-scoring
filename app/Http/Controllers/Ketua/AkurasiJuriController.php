<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Http\Usecases\AkurasiJuriUsecase;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AkurasiJuriController extends Controller
{
    public function index()
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        // KHUSUS KETUA (Role = 2)
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $usecase = new AkurasiJuriUsecase();
        $response = $usecase->getAllAkurasi();
        $akurasiData = $response['success'] ? $response['data']['matches'] : [];
        $eventAccuracy = $response['success'] ? $response['data']['event_accuracy'] : 0;
        $eventJuries = $response['success'] ? $response['data']['event_juries'] : [];

        return view('Ketua.Persentase-juri.index', compact('akurasiData', 'eventAccuracy', 'eventJuries'));
    }

    public function exportPdfAll(Request $request)
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        // KHUSUS KETUA (Role = 2)
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $type = $request->query('type', 'babak'); // babak, partai, event

        $usecase = new AkurasiJuriUsecase();
        $response = $usecase->getAllAkurasi();
        $akurasiData = $response['success'] ? $response['data']['matches'] : [];
        $eventAccuracy = $response['success'] ? $response['data']['event_accuracy'] : 0;
        $eventJuries = $response['success'] ? $response['data']['event_juries'] : [];

        $pdf = Pdf::loadView('Ketua.Persentase-juri.pdf', compact('akurasiData', 'eventAccuracy', 'eventJuries', 'type'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('Laporan_Akurasi_Juri_Seluruh_Pertandingan_' . $type . '.pdf');
    }

    public function exportPdfMatch($id)
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/ketua');
        }

        // KHUSUS KETUA (Role = 2)
        if (session('role') != 2) {
            abort(403, 'Akses ditolak');
        }

        $usecase = new AkurasiJuriUsecase();
        $response = $usecase->getAllAkurasi();
        $allData = $response['success'] ? $response['data']['matches'] : [];
        
        $akurasiData = array_values(array_filter($allData, function($match) use ($id) {
            $mId = is_array($match) ? ($match['match_id'] ?? null) : ($match->match_id ?? null);
            return (string)$mId === (string)$id;
        }));

        if (empty($akurasiData)) {
            $match = \Illuminate\Support\Facades\DB::table('pertandingan')->where('id', $id)->first();
            if ($match) {
                $juris = \Illuminate\Support\Facades\DB::table('akurasi_juri')
                    ->join('petugas_pertandingan', 'akurasi_juri.id_petugas_pertandingan', '=', 'petugas_pertandingan.id')
                    ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                    ->where('akurasi_juri.id_pertandingan', $id)
                    ->select(
                        'petugas_pertandingan.id as id_petugas_pertandingan',
                        'data_petugas.nama as nama_juri',
                        'petugas_pertandingan.posisi',
                        'akurasi_juri.total_input',
                        'akurasi_juri.total_nilai_sah',
                        'akurasi_juri.total_nilai_tidak_sah',
                        'akurasi_juri.persentase_akurasi'
                    )
                    ->get();
                    
                $jList = [];
                foreach ($juris as $j) {
                    $jList[] = [
                        'id_petugas' => $j->id_petugas_pertandingan,
                        'nama_juri' => $j->nama_juri,
                        'posisi' => $j->posisi,
                        'total_input' => $j->total_input,
                        'total_nilai_sah' => $j->total_nilai_sah,
                        'total_nilai_tidak_sah' => $j->total_nilai_tidak_sah,
                        'total_sah_semua_juri' => 0,
                        'persentase_akurasi' => $j->persentase_akurasi,
                        'event_akurasi' => 0,
                        'rounds' => []
                    ];
                }

                $akurasiData = [[
                    'match_id' => $match->id,
                    'partai' => $match->partai,
                    'gelanggang' => $match->gelanggang,
                    'kelas' => $match->kelas,
                    'golongan' => $match->golongan,
                    'tanggal_dihitung' => now(),
                    'juris' => $jList
                ]];
            }
        }

        if (empty($akurasiData)) {
            abort(404, 'Data pertandingan tidak ditemukan');
        }

        // Reset array keys
        $akurasiData = array_values($akurasiData);
        $partai = $akurasiData[0]['partai'];
        $type = 'partai';
        $pdf = Pdf::loadView('Ketua.Persentase-juri.pdf', compact('akurasiData', 'partai', 'type'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('Laporan_Akurasi_Juri_Partai_'.$partai.'.pdf');
    }
}
