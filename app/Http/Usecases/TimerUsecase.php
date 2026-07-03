<?php

namespace App\Http\Usecases;

use Illuminate\Support\Facades\Cache;

class TimerUsecase extends Usecase
{
    private $cacheKey = 'current_timer_state';

    public function syncState(array $data)
    {
        $state = Cache::get($this->cacheKey, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);

        if (isset($data['round'])) {
            $state['round'] = (int) $data['round'];
        }
        if (isset($data['time_remaining'])) {
            $state['time_remaining'] = (int) $data['time_remaining'];
        }
        if (isset($data['status'])) {
            $state['status'] = $data['status'];
        }

        Cache::put($this->cacheKey, $state);

        return ['success' => true, 'state' => $state];
    }

    public function getState()
    {
        return Cache::get($this->cacheKey, [
            'round' => 1,
            'time_remaining' => 120,
            'status' => 'stopped'
        ]);
    }
}
