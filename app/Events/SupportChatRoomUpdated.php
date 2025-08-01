<?php

namespace App\Events;

use App\Http\Resources\V1\ChatRoomResource;
use App\Models\ChatRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportChatRoomUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ChatRoom $room)
    {
        $this->room->load(['participants', 'messages']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // new PrivateChannel($this->message->chatRoom->getChannelName()),
            new Channel('chat.rooms.support'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.rooms.support.updated';
    }

    public function broadcastWith(): array
    {
        return ChatRoomResource::make($this->room)->toArray(request());
    }
}
