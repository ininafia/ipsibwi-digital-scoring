<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;
use App\Http\Usecases\PertandinganUsecase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RiwayatController extends Controller
{
    protected PertandinganUsecase $pertandinganUsecase;

    public function __construct()
    {
        $this->pertandinganUsecase = new PertandinganUsecase();
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'finished');

        if ($tab === 'final') {
            $result = $this->pertandinganUsecase->getFinal();
        } else {
            $result = $this->pertandinganUsecase->getFinished();
        }

        return view('Ketua.Riwayat.index', [
            'list' => $result['data']['list'] ?? [],
            'tab'  => $tab,
        ]);
    }

    public function detail($id)
    {
        $data = $this->getMatchData($id);
        return view('Ketua.Riwayat.detail', $data);
    }

    public function exportPdf($id)
    {
        $data = $this->getMatchData($id);

        $pdf = Pdf::loadView('Operator.finished.pdf-detail', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream("Detail_Skor_Partai_{$data['match']->partai}.pdf");
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

        $allVoteEventIds = [];
        if (!empty($awardIds)) {
            $allVoteRows = DB::table('score_award_votes')
                ->whereIn('award_id', $awardIds)
                ->get();
            foreach ($allVoteRows as $vr) {
                $allVoteEventIds[$vr->score_event_id] = true;
            }
        }

        $allEvents = DB::table('score_events')
            ->where('match_id', $match->id)
            ->whereIn('status', ['consumed', 'expired'])
            ->orderBy('server_time', 'asc')
            ->get();

        // Build window_id mapping for consumed events to identify paired inputs
        $windowMap = [];
        foreach ($allEvents as $evt) {
            if ($evt->status !== 'consumed' || empty($evt->window_id)) {
                continue;
            }
            
            $juriPosisi = null;
            foreach ($juriMap as $pos => $pp_id) {
                if ($pp_id == $evt->judge_id) {
                    $juriPosisi = $pos;
                    break;
                }
            }
            if (!$juriPosisi) continue;
            
            $juriNum = str_replace('juri_', '', $juriPosisi);
            $juriName = 'Juri ' . $juriNum;
            
            $wId = (string) $evt->window_id;
            if (!isset($windowMap[$wId])) {
                $windowMap[$wId] = [
                    'round'   => $evt->round,
                    'athlete' => $evt->athlete,
                    'juris'   => [],
                ];
            }
            if (!in_array($juriName, $windowMap[$wId]['juris'], true)) {
                $windowMap[$wId]['juris'][] = $juriName;
            }
        }

        $pairCounters = [
            1 => ['blue' => 0, 'red' => 0],
            2 => ['blue' => 0, 'red' => 0],
            3 => ['blue' => 0, 'red' => 0],
        ];

        $windowPairs = [];
        foreach ($windowMap as $wId => $info) {
            $r = $info['round'];
            $a = $info['athlete'];
            if (!isset($pairCounters[$r][$a])) {
                $pairCounters[$r][$a] = 0;
            }
            $pairCounters[$r][$a]++;
            $num = $pairCounters[$r][$a];
            
            sort($info['juris']);
            $jurisStr = implode(', ', $info['juris']);
            
            $windowPairs[$wId] = [
                'pair_number' => $num,
                'juris'       => $info['juris'],
                'pair_label'  => "Pasangan #{$num} ({$jurisStr})",
            ];
        }

        $eventHistory = [];
        foreach (['juri_1', 'juri_2', 'juri_3'] as $posisi) {
            for ($r = 1; $r <= 3; $r++) {
                $eventHistory[$posisi][$r] = ['blue' => [], 'red' => []];
            }
        }

        $juriInputCounters = ['juri_1' => 0, 'juri_2' => 0, 'juri_3' => 0];

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
            if ($evt->status === 'consumed') {
                $isSah = isset($allVoteEventIds[$evt->id]);
            }

            $juriInputCounters[$juriPosisi]++;

            $wId = (string) $evt->window_id;
            $pairInfo = ($isSah && isset($windowPairs[$wId])) ? $windowPairs[$wId] : null;

            $eventHistory[$juriPosisi][$evt->round][$evt->athlete][] = [
                'value'       => $evt->score_value,
                'sah'         => $isSah,
                'window_id'   => $evt->window_id,
                'input_index' => $juriInputCounters[$juriPosisi],
                'pair_info'   => $pairInfo,
            ];
        }

        $awardHistory = [];
        for ($r = 1; $r <= 3; $r++) {
            $awardHistory[$r] = ['blue' => [], 'red' => []];
        }
        $awardCounter = ['blue' => 0, 'red' => 0];
        foreach ($awards as $awd) {
            $awardCounter[$awd->athlete]++;
            $wId = (string) $awd->window_id;
            $pairInfo = isset($windowPairs[$wId]) ? $windowPairs[$wId] : null;

            $awardHistory[$awd->round][$awd->athlete][] = [
                'value'       => $awd->score_value,
                'award_id'    => (string) $awd->id,
                'window_id'   => $awd->window_id,
                'input_index' => $awardCounter[$awd->athlete],
                'pair_info'   => $pairInfo,
            ];
        }

        $awardsTotals = [
            'blue' => [1 => ['punch' => 0, 'kick' => 0], 2 => ['punch' => 0, 'kick' => 0], 3 => ['punch' => 0, 'kick' => 0]],
            'red'  => [1 => ['punch' => 0, 'kick' => 0], 2 => ['punch' => 0, 'kick' => 0], 3 => ['punch' => 0, 'kick' => 0]],
        ];
        
        foreach ($awards as $award) {
            $awardsTotals[$award->athlete][$award->round][$award->technique] += $award->score_value;
        }

        $penaltiesPerRound = [
            1 => ['blue' => ['jatuhan' => [], 'hukuman' => [], 'binaan' => []], 'red' => ['jatuhan' => [], 'hukuman' => [], 'binaan' => []]],
            2 => ['blue' => ['jatuhan' => [], 'hukuman' => [], 'binaan' => []], 'red' => ['jatuhan' => [], 'hukuman' => [], 'binaan' => []]],
            3 => ['blue' => ['jatuhan' => [], 'hukuman' => [], 'binaan' => []], 'red' => ['jatuhan' => [], 'hukuman' => [], 'binaan' => []]],
        ];

        $riwayatHukuman = DB::table('riwayat_hukuman')
            ->where('id_pertandingan', $id)
            ->orderBy('id', 'asc')
            ->get();

        $globalCounts = [
            'blue' => ['jatuhan' => 0, 'teguran' => 0, 'peringatan' => 0, 'binaan' => 0],
            'red'  => ['jatuhan' => 0, 'teguran' => 0, 'peringatan' => 0, 'binaan' => 0],
        ];

        foreach ($riwayatHukuman as $rh) {
            $sudut = $rh->sudut === 'biru' ? 'blue' : 'red';
            $babak = $rh->id_babak;
            $jenis = $rh->jenis_hukuman;
            
            if ($babak >= 1 && $babak <= 3) {
                if ($rh->action === 'add') {
                    $globalCounts[$sudut][$jenis]++;
                    $currCount = $globalCounts[$sudut][$jenis];
                    
                    if ($jenis === 'teguran') {
                        $val = ($currCount == 1) ? '-1' : '-2';
                        $penaltiesPerRound[$babak][$sudut]['hukuman'][] = $val;
                    } elseif ($jenis === 'peringatan') {
                        $val = ($currCount == 1) ? '-5' : '-10';
                        $penaltiesPerRound[$babak][$sudut]['hukuman'][] = $val;
                    } elseif ($jenis === 'jatuhan') {
                        $val = ($currCount == 1) ? '3' : '+3';
                        $penaltiesPerRound[$babak][$sudut]['jatuhan'][] = $val;
                    } elseif ($jenis === 'binaan') {
                        $penaltiesPerRound[$babak][$sudut]['binaan'][] = (string)$currCount;
                    }
                } elseif ($rh->action === 'undo') {
                    if ($jenis === 'teguran' || $jenis === 'peringatan') {
                        if (count($penaltiesPerRound[$babak][$sudut]['hukuman']) > 0) {
                            array_pop($penaltiesPerRound[$babak][$sudut]['hukuman']);
                        }
                    } else {
                        if (count($penaltiesPerRound[$babak][$sudut][$jenis]) > 0) {
                            array_pop($penaltiesPerRound[$babak][$sudut][$jenis]);
                        }
                    }
                    if ($globalCounts[$sudut][$jenis] > 0) {
                        $globalCounts[$sudut][$jenis]--;
                    }
                }
            }
        }

        return compact('match', 'skor', 'awardsTotals', 'eventHistory', 'awardHistory', 'namaPetugas', 'penaltiesPerRound');
    }
}
