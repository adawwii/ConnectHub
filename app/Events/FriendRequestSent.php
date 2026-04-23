<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PrivateChannel;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender;
    public $receiver;

    /**
     * Create a new event instance.
     */
    public function __construct(User $sender, User $receiver)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        logger('event triggered');
    }
    

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    // public function broadcastOn(): array
    // {
    //     logger('broadcastOn triggered');
    //     return [
    //         new PrivateChannel('user.'.$this->receiver->id),
    //     ];
    // }
    // public function broadcastWith(): array
    // {
    //     logger('broadcastWith triggered');
    //     return [
    //         'sender_id' => $this->sender->id,
    //         'sender_name' => $this->sender->name,
    //         'message' => "You have a new friend request from {$this->sender->name}."
    //     ];
    // }
}
