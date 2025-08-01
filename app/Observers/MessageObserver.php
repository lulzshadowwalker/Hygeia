<?php

namespace App\Observers;

use App\Events\MessageSent;
use App\Models\Message;

class MessageObserver
{
    public function created(Message $message): void
    {
        MessageSent::dispatch($message);
        
        // manually calling touch to update the chat room's updated_at timestamp
        // because it seems that simply defining $touches in the model does not work as expected
        // and the updated event on the chat room is not being triggered
        $message->chatRoom->touch();
    }
}
