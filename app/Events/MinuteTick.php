<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MinuteTick implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct()
    {
        $this->message = 'Minute tick event triggered at ' . now()->toDateTimeString();
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting minute Message');
        return new Channel('public-minute-channel');
    }

    public function broadcastAs()
    {
        return 'minute-tick';
    }
    public function broadcastWith()
    {
        return ['message' => $this->message]; // Directly return an array
    }
}
