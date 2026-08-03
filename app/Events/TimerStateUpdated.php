<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerStateUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $matchId;
    public $round;
    public $timeRemaining;
    public $status;

    public function __construct($matchId, $round, $timeRemaining, $status)
    {
        $this->matchId = $matchId;
        $this->round = $round;
        $this->timeRemaining = $timeRemaining;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('match.' . $this->matchId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'round' => $this->round,
            'time_remaining' => $this->timeRemaining,
            'status' => $this->status,
        ];
    }
}
