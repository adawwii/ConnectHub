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
