<?php

namespace App\Listeners;

use App\Events\FriendRequestSent;
use App\Jobs\SendFriendRequestNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Queue\InteractsWithQueue;

class SendFriendRequestNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FriendRequestSent $event): void
    {
        SendFriendRequestNotificationJob::dispatch($event->sender,$event->receiver);
    }
}
