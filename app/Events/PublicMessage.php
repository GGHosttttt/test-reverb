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

class PublicMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting Public Message to public-channel');
        return new Channel('public-channel');
    }

    public function broadcastAs()
    {
        return 'PublicMessage'; // Specify the event name
    }
    // In your event
    public function broadcastWith()
    {
        return ['message' => $this->message]; // Directly return an array
    }
}
