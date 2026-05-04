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
            new PrivateChannel('user.'.$this->receiver_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'messageId' => $this->message->id,
            'senderId' => $this->message->user_id,
            'receiverId' => $this->receiver_id
        ];
    }
}
