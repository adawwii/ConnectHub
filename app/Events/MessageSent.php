<?php

namespace App\Events;

use App\Models\Message;
// use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message){}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];
    }

     public function broadcastWith()
    {
        return [
            'chat_id'  => $this->message->chat_id,
            'senderId' => $this->message->user_id,
            'messageData' => [
                'messageId'    => $this->message->id,
                'message'      => $this->message->message,
                'is_sender'    => false,
                'delivered_at' => $this->message->delivered_at,
                'seen_at'      => $this->message->seen_at,
            ]
        ];
    }
}
