<?php

namespace App\Http\Controllers;

use App\Services\ContactsService;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    protected $contactsService;

    public function __construct(ContactsService $contactsService)
    {
        $this->contactsService = $contactsService;
    }
    //add contact
    public function create(Request $request)
    {
       $sender= auth()->user();
       $receiver = $this->contactsService->addFriend($request, $sender);
       if($receiver['type'] === 'failed')
        {
            session()->flash('info',$receiver["message"]);
        } else{
            session()->flash('success',$receiver["message"]);
            }
        return response()->json([
            'response'=>$receiver['message']
        ]);
    }
}
