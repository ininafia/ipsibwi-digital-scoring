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
        
        $oldState = Cache::get($cacheKey, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);

        $state = $oldState;

        if (isset($data['round'])) {
            $round = (int) $data['round'];
            if ($round >= 1 && $round <= 3) {
                $state['round'] = $round;
            } else {
                return ['success' => false, 'error' => 'Invalid round', 'code' => 422];
            }
        }
        
        if (isset($data['time_remaining'])) {
            $time = (int) $data['time_remaining'];
            if ($time >= 0) {
                $state['time_remaining'] = $time;
            } else {
                return ['success' => false, 'error' => 'Invalid time_remaining', 'code' => 422];
            }
        }
        
        if (isset($data['status'])) {
            $status = $data['status'];
            if (in_array($status, ['playing', 'paused', 'stopped'])) {
                $state['status'] = $status;
            } else {
                return ['success' => false, 'error' => 'Invalid status', 'code' => 422];
            }
        }

        $shouldBroadcast = false;
        
        if ($oldState['round'] !== $state['round'] || $oldState['status'] !== $state['status']) {
            $shouldBroadcast = true;
        }

        // Sinkronisasi waktu setiap kelipatan 10 detik agar tidak drift
        // atau jika ada perbedaan signifikan (tapi timer client cukup akurat)
        if ($oldState['status'] === 'playing' && $state['status'] === 'playing') {
            if ($state['time_remaining'] % 15 === 0) {
                $shouldBroadcast = true;
            }
        }

        Cache::put($cacheKey, $state);

        return ['success' => true, 'state' => $state, 'should_broadcast' => $shouldBroadcast, 'code' => 200];
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
