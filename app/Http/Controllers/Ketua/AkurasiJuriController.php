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
        
        // Filter array for the specific match_id
        $akurasiData = array_filter($allData, function($match) use ($id) {
            return $match['match_id'] == $id;
        });

        if (empty($akurasiData)) {
            abort(404, 'Data pertandingan tidak ditemukan');
        }

        // Reset array keys
        $akurasiData = array_values($akurasiData);
        $partai = $akurasiData[0]['partai'];

        $pdf = Pdf::loadView('Ketua.Persentase-juri.pdf', compact('akurasiData', 'partai'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('Laporan_Akurasi_Juri_Partai_'.$partai.'.pdf');
    }
}
