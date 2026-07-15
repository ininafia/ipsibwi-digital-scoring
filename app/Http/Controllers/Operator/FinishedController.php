<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FinishedController extends Controller
{
    public function detail($id)
    {
        $data = $this->getMatchData($id);
        return view('Operator.finished.detail', $data);
    }

    public function exportPdf($id)
    {
        $data = $this->getMatchData($id);
        $pdf = Pdf::loadView('Operator.finished.pdf-detail', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download("Detail_Skor_Partai_{$data['match']->partai}.pdf");
    }

    private function getMatchData($id)
    {
        $match = DB::table('pertandingan')
            ->where('id', $id)
            ->first();

        if (!$match) {
            abort(404, 'Pertandingan tidak ditemukan');
        }

        $skor = DB::table('skor_pertandingan')
            ->where('id_pertandingan', $id)
            ->first();

        $awards = DB::table('score_awards')
            ->where('match_id', $id)
            ->get();
            
        $awardIds = $awards->pluck('id')->toArray();

        $juris = DB::table('petugas_pertandingan')
            ->where('id_pertandingan', $match->id)
            ->where('id_role', 5)
            ->select('id as pp_id', 'posisi')
            ->get();
            
        $juriMap = [];
        foreach ($juris as $j) {
            $juriMap[$j->posisi] = $j->pp_id;
        }

        $semuaPetugas = DB::table('petugas_pertandingan')
            ->join('data_petugas', 'petugas_pertandingan.id_petugas', '=', 'data_petugas.id')
            ->where('petugas_pertandingan.id_pertandingan', $match->id)
            ->select('petugas_pertandingan.id_role', 'petugas_pertandingan.posisi', 'data_petugas.nama')
            ->get();
            
        $namaPetugas = [
            'ketua' => '-',
            'dewan' => '-',
            'juri_1' => '-',
            'juri_2' => '-',
            'juri_3' => '-'
        ];

        foreach ($semuaPetugas as $p) {
            if ($p->id_role == 2) {
                $namaPetugas['ketua'] = $p->nama;
            } elseif ($p->id_role == 3) {
                $namaPetugas['dewan'] = $p->nama;
            } elseif ($p->id_role == 5) {
                if ($p->posisi == 'juri_1') $namaPetugas['juri_1'] = $p->nama;
                if ($p->posisi == 'juri_2') $namaPetugas['juri_2'] = $p->nama;
                if ($p->posisi == 'juri_3') $namaPetugas['juri_3'] = $p->nama;
            }
        }

        $allVoteJudgeIds = [];
        if (!empty($awardIds)) {
            $allVoteRows = DB::table('score_award_votes')->whereIn('award_id', $awardIds)->get();
            foreach ($allVoteRows as $vr) {
                $allVoteJudgeIds[$vr->award_id . '_' . $vr->judge_id] = true;
            }
        }

        $allEvents = DB::table('score_events')
            ->where('match_id', $match->id)
            ->whereIn('status', ['consumed', 'expired'])
            ->orderBy('server_time', 'asc')
            ->get();

        $eventHistory = [];
        foreach (['juri_1', 'juri_2', 'juri_3'] as $posisi) {
            for ($r = 1; $r <= 3; $r++) {
                $eventHistory[$posisi][$r] = ['blue' => [], 'red' => []];
            }
        }

        foreach ($allEvents as $evt) {
            $juriPosisi = null;
            foreach ($juriMap as $pos => $pp_id) {
                if ($pp_id == $evt->judge_id) {
                    $juriPosisi = $pos;
                    break;
                }
            }
            if (!$juriPosisi) continue;

            $isSah = false;
            if ($evt->status === 'consumed' && $evt->award_id) {
                $key = $evt->award_id . '_' . $evt->judge_id;
                $isSah = isset($allVoteJudgeIds[$key]);
            }

            $eventHistory[$juriPosisi][$evt->round][$evt->athlete][] = [
                'value' => $evt->score_value,
                'sah' => $isSah,
                'award_id' => $evt->award_id,
            ];
        }

        $awardHistory = [];
        for ($r = 1; $r <= 3; $r++) {
            $awardHistory[$r] = ['blue' => [], 'red' => []];
        }
        foreach ($awards as $awd) {
            $awardHistory[$awd->round][$awd->athlete][] = [
                'value' => $awd->score_value,
                'award_id' => (string) $awd->id,
            ];
        }

        $awardsTotals = [
            'blue' => [1 => ['punch' => 0, 'kick' => 0], 2 => ['punch' => 0, 'kick' => 0], 3 => ['punch' => 0, 'kick' => 0]],
            'red'  => [1 => ['punch' => 0, 'kick' => 0], 2 => ['punch' => 0, 'kick' => 0], 3 => ['punch' => 0, 'kick' => 0]],
        ];
        
        foreach ($awards as $award) {
            $awardsTotals[$award->athlete][$award->round][$award->technique] += $award->score_value;
        }

        return compact('match', 'skor', 'awardsTotals', 'eventHistory', 'awardHistory', 'namaPetugas');
    }
}
