<?php

namespace App\Events;

use App\Http\Resources\V1\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->message->chatRoom->getChannelName()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return MessageResource::make($this->message)->toArray(request());
    }
}
