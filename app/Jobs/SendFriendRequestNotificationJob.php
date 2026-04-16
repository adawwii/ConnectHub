<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\FriendRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendFriendRequestNotificationJob implements ShouldQueue
{
    use Queueable;

    public $sender;
    public $receiver;

    /**
     * Create a new job instance.
     */
    public function __construct(User $sender, User $receiver)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->receiver->notify(new FriendRequestNotification($this->sender));
    }
}
