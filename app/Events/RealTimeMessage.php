<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RealTimeMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $message;

    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('user.' . $this->userId);
        Log::info('Event: Broadcasting to', ['channel' => $channel->name]);
        return $channel;
    }
    public function broadcastWith()
    {
        return [
            'data' => [  // Wrap in 'data' key for consistency
                'message' => $this->message,
                'user_id' => $this->userId
            ]
        ];
    }
    public function broadcastAs()
    {
        $eventName = 'real-time.message';
        Log::info('Event: Broadcasting as', ['event' => $eventName]);
        return $eventName;
    }
}
