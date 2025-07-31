<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreChatMessageRequest;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enums\MessageType;
use App\Http\Resources\V1\MessageResource;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ChatMessageController extends Controller
{
    public function index(ChatRoom $chatRoom)
    {
        //  TODO: Use policies instead
        if (!$chatRoom->participants->contains(auth()->user())) {
            throw new AccessDeniedHttpException('You are not a participant of this chat room.');
        }

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
            ]
        ]);
    }

    public function store(StoreChatMessageRequest $request, ChatRoom $chatRoom)
    {
        // TODO: use policy instead
        if (!$chatRoom->participants->contains(auth()->user())) {
            throw new AccessDeniedHttpException('You are not a participant of this chat room.');
        }

        $message = Message::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => auth()->id(),
            'content' => $request->content(),
            'type' => $request->type(),
        ]);

        $message->load('user');

        MessageSent::dispatch($message);

        return response()->json([
            'data' => new MessageResource($message)
        ], 201);
    }
}
