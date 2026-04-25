<?php

namespace App\Jobs;

use App\Models\Contacts;
use App\Models\User;
use App\Notifications\FriendRequestAcceptedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AcceptFriendRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $senderId;
    protected $receiverId;

    /**
     * Create a new job instance.
     */
    public function __construct($senderId, $receiverId)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $contact = Contacts::where('sender_id', $this->senderId)
            ->where('receiver_id', $this->receiverId)
            ->first();

        if ($contact && ($contact->status === 'pending' || $contact->status === 'accepted')) {
            if ($contact->status === 'pending') {
                $contact->update(['status' => 'accepted']);
            }

            $sender = User::find($this->senderId);
            $receiver = User::find($this->receiverId);

            if ($sender && $receiver) {
                $sender->notify(new FriendRequestAcceptedNotification($receiver));
            }
        }
    }
}
