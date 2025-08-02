<?php

namespace App\Observers;

use App\Enums\ChatRoomType;
use App\Events\SupportChatRoomCreated;
use App\Events\SupportChatRoomUpdated;
use App\Models\ChatRoom;

class ChatRoomObserver
{
    /**
     * Handle the ChatRoom "created" event.
     */
    public function created(ChatRoom $chatRoom): void
    {
        if ($chatRoom->type === ChatRoomType::Support) {
            SupportChatRoomCreated::dispatch($chatRoom);
        }
    }

    /**
     * Handle the ChatRoom "updated" event.
     */
    public function updated(ChatRoom $chatRoom): void
    {
        // TODO: Add unit tests
        if ($chatRoom->type === ChatRoomType::Support) {
            SupportChatRoomUpdated::dispatch($chatRoom);
        }
    }
}
