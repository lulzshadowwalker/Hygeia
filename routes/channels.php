<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//  TODO: Restrict access to users who belong to the chat room
//  TODO: Handle room authorization (from policy) to e.g. booking chat rooms that do not have a status of **confirmed**
Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    return true;

    return $user->chatRooms()->where('id', $roomId)->exists();
});
