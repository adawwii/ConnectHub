<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class FriendRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $sender_id;
    public string $sender_name;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $sender)
    {
        $this->sender_id = $sender->id;
        $this->sender_name = $sender->name;
        logger("notification triggered for sender: {$this->sender_name} with id: {$this->sender_id}");
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender_name,
            'message' => "You have a new friend request from {$this->sender_name}."
        ];
    }
    

    /**
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
     {
        return new BroadcastMessage([
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender_name,
            'message' => "You have a new friend request from {$this->sender_name}."
        ]);
    }
    
}
