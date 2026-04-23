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
        logger("Job created for sender: {$sender->name} with id: {$sender->id} to receiver: {$receiver->name} with id: {$receiver->id}");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger("Job handling for sender: {$this->sender->name} with id: {$this->sender->id} to receiver: {$this->receiver->name} with id: {$this->receiver->id}");
        $this->receiver->notify(new FriendRequestNotification($this->sender));
    }
}
