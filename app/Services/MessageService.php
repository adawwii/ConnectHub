<?php

namespace App\Services;

use App\Events\MessageDelivered;
use App\Events\MessageSeen as MessageSeenEvent;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function send(Request $request, User $sender)
    {
        $receiver = $request->receiver_id;
        $chat = Chat::between($sender->id, $receiver)->first();
        
        $message = $this->message->create([
            'chat_id' => $chat->id,
            'user_id' => $sender->id,
            'message' => $request->message,
        ]);

        event(new MessageSent($message));
        event(new MessageDelivered($message, $receiver));

        return $message;
    }

    public function fallback(User $receiver)
    {
        $this->message->where('user_id', '!=', $receiver->id)
            ->whereHas('chat', function ($query) use ($receiver) {
                $query->whereHas('users', function ($q) use ($receiver) {
                    $q->where('users.id', $receiver->id);
                });
            })
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        return;
    }

    public function messageSeen(Request $request)
    {
        $updated = $this->message
            ->where('id', $request->message)
            ->where('user_id', '!=', auth()->id())
            ->update([
                'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())'),
                'seen_at'      => DB::raw('COALESCE(seen_at, NOW())'),
            ]);

        if ($updated) {
            $message = $this->message->find($request->message);
            event(new MessageSeenEvent($message));
        }

        return;
    }

    public function messageDelivered(Request $request)
    {
        $updated = $this->message
            ->where('id', $request->messageId)
            ->update([
                'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())'),
            ]);

        if ($updated) {
            $message = $this->message->find($request->messageId);
            event(new MessageSeenEvent($message));
        }

        return;
    }

    public function markChatAsSeen($chatId)
    {
        $userId = auth()->id();
        $now = now();

        $unread = $this->message
            ->where('chat_id', $chatId)
            ->where('user_id', '!=', $userId)
            ->whereNull('seen_at')
            ->get();

        if ($unread->isNotEmpty()) {
            $this->message
                ->whereIn('id', $unread->pluck('id'))
                ->update([
                    'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())'),
                    'seen_at'      => $now,
                ]);

            foreach ($unread as $msg) {
                $msg->seen_at = $now;
                if (!$msg->delivered_at) {
                    $msg->delivered_at = $now;
                }
                event(new MessageSeenEvent($msg));
            }
        }
    }
}
