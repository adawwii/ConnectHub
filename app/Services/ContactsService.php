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
}
