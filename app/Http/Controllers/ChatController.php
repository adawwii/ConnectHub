<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ChatService;


// use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    //
    public function index()
    {
        $contacts = $this->chatService->index();
        
        return view('chat',compact('contacts'));
    }
    // retrive or create chat data 
    public function openChat(User $friend)
    {
        $before = request()->query('before');
        $messages = $this->chatService->chatData($friend, $before);
        return response()->json($messages);
    }
}
