<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\SupportChatRoomUpdated;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreChatMessageRequest;
use App\Http\Resources\V1\MessageResource;
use App\Models\ChatRoom;
use App\Models\Message;

class ChatMessageController extends ApiController
{
    public function index(ChatRoom $chatRoom)
    {
        $this->authorize('viewMessages', [Message::class, $chatRoom]);

        $messages = $chatRoom->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'data' => MessageResource::collection($messages),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
            'links' => [
                'first' => $messages->url(1),
                'last' => $messages->url($messages->lastPage()),
                'prev' => $messages->previousPageUrl(),
                'next' => $messages->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreChatMessageRequest $request, ChatRoom $chatRoom)
    {
        $this->authorize('sendMessage', [Message::class, $chatRoom]);

        $message = Message::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => auth()->id(),
            'content' => $request->content(),
            'type' => $request->type(),
        ]);

        $message->load('user');

        SupportChatRoomUpdated::dispatch($chatRoom);

        return MessageResource::make($message);
    }
}
