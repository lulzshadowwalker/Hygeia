<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ChatRoomType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\ChatRoomResource;
use App\Models\Booking;
use App\Models\ChatRoom;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Chat')]
class BookingChatRoomController extends ApiController
{
    /**
     * Create a chat room
     *
     * Create a new chat room.
     */
    public function store(Booking $booking)
    {
        $this->authorize('create', [ChatRoom::class, $booking]);

        $chatRoom = ChatRoom::firstOrCreate([
            'type' => ChatRoomType::Standard,
            'booking_id' => $booking->id,
        ]);

        $participants = [
            $booking->client->user_id,
            $booking->cleaner->user_id,
        ];
        $existingParticipantIds = $chatRoom->participants()->pluck('user_id')->toArray();
        $participants = array_diff($participants, $existingParticipantIds);
        $chatRoom->participants()->attach($participants);
        $chatRoom->load('participants');

        return ChatRoomResource::make($chatRoom);
    }
}
