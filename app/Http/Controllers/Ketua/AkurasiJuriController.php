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

        return view('Ketua.Persentase-Juri.index', compact('akurasiData', 'eventAccuracy'));
    }

    public function exportPdfAll()
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

        $pdf = Pdf::loadView('Ketua.Persentase-Juri.pdf', compact('akurasiData', 'eventAccuracy'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('Laporan_Akurasi_Juri_Seluruh_Pertandingan.pdf');
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

        $pdf = Pdf::loadView('Ketua.Persentase-Juri.pdf', compact('akurasiData'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('Laporan_Akurasi_Juri_Partai_'.$partai.'.pdf');
    }
}
