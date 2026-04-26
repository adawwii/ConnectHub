<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    //
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    //send message
    public function sendMessage(Request $request)
    {
        
        $message = $this->messageService->send($request,auth()->user());
       
        return response()->json($message);
    }
}
