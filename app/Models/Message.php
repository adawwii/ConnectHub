<?php

namespace App\Models;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['chat_id', 'user_id', 'message', 'delivered_at', 'seen_at'];

    protected $casts = [
        'delivered_at' => 'datetime',
        'seen_at'      => 'datetime',
    ];
    //RelationShips
    public function chat(){
        return $this->belongsTo(Chat::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
