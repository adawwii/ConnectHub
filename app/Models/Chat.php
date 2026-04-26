<?php

namespace App\Models;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //scope function for common users chat
    public function scopeBetween(Builder $query, $userId1, $userId2)
{
    return $query->whereHas('users', function ($q) use ($userId1) {
        $q->where('user_id', $userId1);
    })->whereHas('users', function ($q) use ($userId2) {
        $q->where('user_id', $userId2);
    });
}

    //Relationships
    public function users(){
        return $this->belongsToMany(User::class);
    }
    public function messages(){
        return $this->hasMany(Message::class);
    }
}
