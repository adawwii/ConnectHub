<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;


class ChatService
{
    /**
     * Create a new class instance.
     */
    public $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    //view index chat page data
    public function index()
    { 
        $user = auth()->user();
        $friends = $user->friends;
        $chats = $user->chats()->with(['latestMessage', 'users'])->get();

        return $friends->map(function($friend) use ($chats) 
        {
            $chat = $chats->first(function($c) use ($friend) {
                return $c->users->contains('id', $friend->id);
            });
                
            if ($chat) {
                $friend->chat_id = $chat->id;
                $lastMsg = $chat->latestMessage;
                $friend->last_message = $lastMsg?->message;
                $friend->last_message_sender_id = $lastMsg?->user_id;
                $friend->last_message_status = [
                    'delivered' => $lastMsg?->deliverd_at,
                    'seen' => $lastMsg?->seen_at,
                ];
                $friend->last_activity = $lastMsg ? $lastMsg->created_at : $friend->friendship_created_at;
            } else {
                
                $friend->last_message = null;
                $friend->last_message_sender_id = null;
                $friend->last_message_status = ['delivered' => null, 'seen' => null];
                $friend->last_activity = $friend->friendship_created_at;
            }
            return $friend;
        })->sortByDesc('last_activity')->values();
    }

    //retrieve or create chat 
    public function chatData(User $friend)
    {
        $onlineUser= auth()->user();
        $chat = $this->chat->between($onlineUser->id, $friend->id)->first();
        if (!$chat) {
           $chat = Chat::create();
           $chat->users()->syncWithoutDetaching([$onlineUser->id, $friend->id]);
        }
        $messages=$chat->messages()
        ->oldest()
        ->get();
        if($messages){
             $messagesContent =['chat_id' => $chat->id , 'messageData' => $messages->map(function ($singleMessage) use ($onlineUser) {
        return [
            'messageId'    => $singleMessage->id,
            'is_sender'    => $singleMessage->user_id === $onlineUser->id,
            'message'      => $singleMessage->message,
            'delivered_at' => $singleMessage->delivered_at,
            'seen_at'      => $singleMessage->seen_at,
        ];
    })];
        }
        else {
            $messagesContent = ['chat_id' => $chat->id, 'messageData' => [] ];
        }
        
        return $messagesContent;
    }



    
}
