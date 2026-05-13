<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SidebarUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public $receiverId,
        public $senderId,
        public $messageText,
        public $chatId,
        public $unreadCount = 0
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SidebarUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'receiverId'   => $this->receiverId,
            'senderId'     => $this->senderId,
            'messageText'  => $this->messageText,
            'chatId'       => $this->chatId,
            'unreadCount'  => $this->unreadCount,
        ];
    }
}
