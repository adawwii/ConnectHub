<?php

namespace App\Events;

use App\Models\Message;
// use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDelivered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message, public int $receiver_id){}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
            new PrivateChannel('user.' . $this->message->user_id),
            new PrivateChannel('user.' . $this->receiver_id),
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
