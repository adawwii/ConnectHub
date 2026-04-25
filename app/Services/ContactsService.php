<?php

namespace App\Services;

use App\Events\FriendRequestSent;
use App\Models\Contacts;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactsService
{
    protected $contacts;
    /**
     * Create a new class instance.
     */
    public function __construct(Contacts $contacts)
    {
        $this->contacts = $contacts;
    }

    //add Friend
    public function addFriend(Request $request,User $sender)
    {
        $receiver_mail=$request->email;
        
        

        $receiver=User::where('email',$receiver_mail)->first();
        if ($receiver->id == $sender->id) {
            return $attempt= ['type' => 'failed', 'message'=>'Cant add your own account!'];
            }
        
        if (!$receiver) {
            return $attempt= ['type' => 'failed', 'message'=>'Account doesnt exist'];
            }
        $alreadyAdded=$this->contacts->where('sender_id',$sender->id)->where('receiver_id',$receiver->id)->first();
            if ($alreadyAdded) {
               return $alreadyAdded->status === 'pending' 
                ? $attempt=['type' => 'failed', 'message'=>'Pending Request']
                : $attempt=['type' => 'failed', 'message'=>'This account is on your friends list'];
            }
            DB::beginTransaction();
        try{
        $contact=$this->contacts->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            ]);
        DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $attempt=['type' => 'failed', 'message'=>"$e->getMessage()"];
        }
        
        event(new FriendRequestSent($sender,$receiver));
       
        $receiver_name=$receiver->name;
        
        return $attempt = ['type' => 'success', 'message'=>"Friend Request sent to $receiver_name"];
    }
    // Get unread notifications
    public function getUnreadNotifications(User $user)
    {
        return $user->unreadNotifications;
    }

    // Accept Friend Request
    public function acceptRequest($notificationId, User $user)
    {
        $notification = $user->notifications()->findOrFail($notificationId);
        $senderId = $notification->data['sender_id'];

        $contact = $this->contacts->where('sender_id', $senderId)
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($contact) {
            // Update status immediately for snappy UI
            $contact->update(['status' => 'accepted']);
            $notification->markAsRead();

            // Dispatch job for the background notification work
            \App\Jobs\AcceptFriendRequestJob::dispatch($senderId, $user->id);

            $sender = User::find($senderId);
            return ['type' => 'success', 'message' => 'Friend request accepted', 'contact' => $sender];
        }

        return ['type' => 'failed', 'message' => 'Request not found or already processed'];
    }

    // Reject Friend Request
    public function rejectRequest($notificationId, User $user)
    {
        $notification = $user->notifications()->findOrFail($notificationId);
        $senderId = $notification->data['sender_id'];

        $contact = $this->contacts->where('sender_id', $senderId)
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($contact) {
            $contact->delete();
            $notification->markAsRead();
            return ['type' => 'success', 'message' => 'Friend request rejected'];
        }

        return ['type' => 'failed', 'message' => 'Request not found or already processed'];
    }
}
