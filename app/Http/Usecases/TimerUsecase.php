<?php

namespace App\Http\Usecases;

use Illuminate\Support\Facades\Cache;

class TimerUsecase extends Usecase
{
    private function getCacheKey(int $id_pertandingan): string
    {
        return 'current_timer_state_' . $id_pertandingan;
    }

    public function syncState(int $id_pertandingan, array $data)
    {
        $cacheKey = $this->getCacheKey($id_pertandingan);
        
        $state = Cache::get($cacheKey, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);

        if (isset($data['round'])) {
            $round = (int) $data['round'];
            if ($round >= 1 && $round <= 3) {
                $state['round'] = $round;
            }
        }
        
        if (isset($data['time_remaining'])) {
            $time = (int) $data['time_remaining'];
            if ($time >= 0) {
                $state['time_remaining'] = $time;
            }
        }
        
        if (isset($data['status'])) {
            $status = $data['status'];
            if (in_array($status, ['playing', 'paused', 'stopped'])) {
                $state['status'] = $status;
            }
        }

        Cache::put($cacheKey, $state);

        return ['success' => true, 'state' => $state];
    }

    public function getState(int $id_pertandingan)
    {
        $cacheKey = $this->getCacheKey($id_pertandingan);
        return Cache::get($cacheKey, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);
    }
}
