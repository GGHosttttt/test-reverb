<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $chatId;
    public $message;

    public function __construct($userId, $chatId, $message)
    {
        $this->userId = $userId;
        $this->chatId = $chatId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('chat.' . $this->chatId);
        Log::info('ChatMessage: Broadcasting to', ['channel' => $channel->name]);
        return $channel;
    }

    public function broadcastAs()
    {
        $eventName = 'chat.message';
        Log::info('ChatMessage: Broadcasting as', ['event' => $eventName]);
        return $eventName;
    }
}
