<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class FriendRequestAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public int $receiver_id;
    public string $receiver_name;
    public function __construct(User $receiver)
    {
        $this->receiver_id = $receiver->id;
        $this->receiver_name = $receiver->name;
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
            'sender_id' => $this->receiver_id,
            'sender_name' => $this->receiver_name,
            'message' => "{$this->receiver_name} accepted your friend request.",
            'data_type' => 'friend_request_accepted'
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'sender_id' => $this->receiver_id,
            'sender_name' => $this->receiver_name,
            'message' => "{$this->receiver_name} accepted your friend request.",
            'data_type' => 'friend_request_accepted'
        ]);
    }
}
