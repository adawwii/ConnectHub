<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSeen implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
            new PrivateChannel('user.' . $this->message->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'messageId'    => $this->message->id,
            'chatId'       => $this->message->chat_id,
            'senderId'     => $this->message->user_id,
            'delivered_at' => $this->message->delivered_at?->toIso8601String(),
            'seen_at'      => $this->message->seen_at?->toIso8601String(),
        ];
    }
}
