<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->chats->contains($chatId);
});

Broadcast::channel('user-status.{userId}', function ($user, $userId){
    if ($user->id == $userId || $user->isFriendsWith($userId)) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }
    return false;
});