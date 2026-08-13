<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Usecases\JuriUsecase;

class JuriController extends Controller
{
    protected $usecase;

    private const ALLOWED_ROLES = [5];

    public function __construct(JuriUsecase $usecase)
    {
        $this->usecase = $usecase;
    }

    /**
     * Guard untuk endpoint AJAX (input-score, delete-score, history).
     * Mengembalikan JSON 401 kalau belum login/role salah, supaya frontend
     * (fetch + res.json()) tidak diam-diam gagal karena dapat halaman redirect.
     */
    private function requireJuriAjax(): ?JsonResponse
    {
        if (!session('user_id') || !in_array(session('role'), self::ALLOWED_ROLES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return null;
    }

    public function index(Request $request): View|Response|RedirectResponse
    {
        // HARUS LOGIN
        if (!session('user_id')) {
            return redirect('/login/juri');
        }

        $role = session('role');

        // KHUSUS JURI (Role = 5, 6, 7)
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            abort(403, 'Akses ditolak');
        }

        $routeName = $request->route()->getName(); // 'juri1', 'juri2', 'juri3'
        $juriNumber = str_replace('juri', '', $routeName); // '1', '2', '3'
        // Default fallback to 1 if route is something else
        if (!in_array($juriNumber, ['1', '2', '3'])) {
            $juriNumber = '1';
        }
        $posisiTarget = 'juri_' . $juriNumber;

        // Verifikasi bahwa user yang login memang ditugaskan di posisi ini
        $sessionJuriPosition = session('juri_position');
        if ($sessionJuriPosition && $sessionJuriPosition !== $posisiTarget) {
            abort(403, 'Anda login sebagai ' . strtoupper(str_replace('_', ' ', $sessionJuriPosition)) . ', bukan ' . strtoupper(str_replace('_', ' ', $posisiTarget)));
        }

        $match = DB::table('pertandingan')
            ->where('status', 'playing')
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->first();

        $namaPosisi = 'JURI ' . $juriNumber;
        $namaPetugas = 'MENUNGGU PENUGASAN';
        
        if ($match) {
            $assignment = DB::table('petugas_pertandingan')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('petugas_pertandingan.id_pertandingan', $match->id)
                ->where('petugas_pertandingan.posisi', $posisiTarget)
                ->first(['data_petugas.nama']);
                
            if ($assignment) {
                $namaPetugas = strtoupper($assignment->nama);
            }
        }

        return view('Juri.index', compact('namaPosisi', 'namaPetugas', 'match', 'posisiTarget', 'juriNumber'));
    }

    public function inputScore(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }
        $response = $this->usecase->inputScore($request);
        if ($response['success'] ?? false) {
            // BUG-7 FIX: Gunakan id_pertandingan (bukan match_id yang selalu null)
            // agar scoreboard update real-time setelah juri input nilai
            event(new \App\Events\MatchUpdated($request->id_pertandingan));
        }
        return response()->json($response);
    }

    public function deleteScore(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }
        $response = $this->usecase->deleteScore($request);
        if ($response['success'] ?? false) {
            // BUG-7 FIX: Gunakan id_pertandingan (bukan match_id yang selalu null)
            event(new \App\Events\MatchUpdated($request->id_pertandingan));
        }
        return response()->json($response);
    }

    public function getHistory(Request $request)
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }
        return response()->json($this->usecase->getHistory($request));
    }

    public function uploadVideo(Request $request): JsonResponse
    {
        if ($unauthorized = $this->requireJuriAjax()) {
            return $unauthorized;
        }

        $request->validate([
            'video'           => 'required|file|max:102400',
            'id_pertandingan' => 'required|numeric',
            'posisi_juri'     => 'required|string',
            'duration'        => 'nullable|numeric',
        ]);

        try {
            $file = $request->file('video');
            $matchId = $request->input('id_pertandingan');
            $posisiJuri = $request->input('posisi_juri');
            $duration = (int) $request->input('duration', 0);

            $ext = $file->getClientOriginalExtension() ?: 'webm';
            $filename = 'juri_' . $posisiJuri . '_match_' . $matchId . '_' . time() . '.' . $ext;
            
            $destinationPath = public_path('uploads/videos/juri');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);
            $filePath = 'uploads/videos/juri/' . $filename;

            $namaJuri = session('username') ?? 'Juri';
            $assignment = DB::table('petugas_pertandingan')
                ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
                ->where('petugas_pertandingan.id_pertandingan', $matchId)
                ->where('petugas_pertandingan.posisi', $posisiJuri)
                ->first(['data_petugas.nama', 'data_petugas.id']);

            if ($assignment) {
                $namaJuri = $assignment->nama;
            }

            $recordId = DB::table('video_juri_logs')->insertGetId([
                'id_pertandingan' => $matchId,
                'posisi_juri'     => $posisiJuri,
                'id_petugas'      => $assignment->id ?? session('user_id'),
                'nama_juri'       => $namaJuri,
                'filename'        => $filename,
                'file_path'       => $filePath,
                'duration_seconds'=> $duration,
                'file_size'       => filesize($destinationPath . '/' . $filename),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Video aktivitas juri berhasil diunggah',
                'data'      => [
                    'id'        => $recordId,
                    'file_path' => asset($filePath),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah video: ' . $e->getMessage()
            ], 500);
        }
    }
}