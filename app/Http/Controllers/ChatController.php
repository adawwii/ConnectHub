<?php

namespace App\Http\Controllers;

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
    public function index(){
        $user=auth()->user();
        $contacts=$user->friends;
        // dd($contacts);
        return view('chat',compact('contacts'));
    }
}
