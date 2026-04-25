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

    // List notifications
    public function notifications()
    {
        $notifications = $this->contactsService->getUnreadNotifications(auth()->user());
        return response()->json($notifications);
    }

    // Accept friend request
    public function acceptNotification($id)
    {
        $result = $this->contactsService->acceptRequest($id, auth()->user());
        return response()->json($result);
    }

    // Reject friend request
    public function rejectNotification($id)
    {
        $result = $this->contactsService->rejectRequest($id, auth()->user());
        return response()->json($result);
    }
}
