<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageService
{
    /**
     * Create a new class instance.
     */
    public $message;
    
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function send(Request $request,User $sender)
    {
    
        $receiver=$request->receiver_id;
        $chat = Chat::between($sender->id,$receiver)->first();
        $message = $this->message->create([
            'chat_id' => $chat->id,
            'user_id' => $sender->id,
            'message' => $request->message,
        ]);
        
       event(new MessageSent($message));

        return $message;
    }
}
