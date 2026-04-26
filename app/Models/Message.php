<?php

namespace App\Models;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['chat_id', 'user_id', 'message'];
    //RelationShips
    public function chat(){
        return $this->belongsTo(Chat::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
