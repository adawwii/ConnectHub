<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Chat;
use App\Models\Contacts;
use App\Models\Message;
use App\Traits\HasActivityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasActivityStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function isFriendsWith($userId)
    {
        return Contacts::where('status', 'accepted')
        ->where(function($query) use ($userId) {
            $query->where([['sender_id', $this->id], ['receiver_id', $userId]])
            ->orWhere([['sender_id', $userId], ['receiver_id', $this->id]]);
        })
        ->exists();
    }

    //attributes
    
    public function getFriendsAttribute()
    {
        $this->loadMissing([
            'sentContacts.receiver', 
            'receivedContacts.sender'
            ]);
            
            $sent = $this->sentContacts->where('status', 'accepted')->map(function($contact) {
                $friend = $contact->receiver;
                $friend->friendship_created_at = $contact->created_at; 
                return $friend;
            });
            
            $received = $this->receivedContacts->where('status', 'accepted')->map(function($contact) {
                 $friend = $contact->sender;
                 $friend->friendship_created_at = $contact->created_at; 
                 return $friend;
                });
            
            return $sent->concat($received);
    }

    //RelationShips
    public function messages(){
        return $this->hasmany(Message::class);
    }
    public function chats(){
        return $this->belongsToMany(Chat::class);
    }
    public function sentContacts(){
        return $this->hasMany(Contacts::class,'sender_id');
    }
    public function receivedContacts(){
        return $this->hasMany(Contacts::class,'receiver_id');
    }

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.'.$this->id;
    }
}
