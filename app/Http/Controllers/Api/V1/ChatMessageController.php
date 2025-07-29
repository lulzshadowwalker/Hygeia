<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enums\MessageType;

class ChatMessageController extends Controller
{
    public function index(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        if (!$chatRoom->participants->contains($user)) {
            return response()->json(['message' => 'Access denied'], 403);
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

    public function store(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        if (!$chatRoom->participants->contains($user)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:10000',
            'type' => ['required', Rule::in(MessageType::cases())]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = Message::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'content' => $request->input('content'),
            'type' => $request->input('type'),
        ]);

        // Touch the chat room to update its updated_at timestamp
        $chatRoom->touch();

        $message->load('user');

        // Dispatch the event
        MessageSent::dispatch($message);

        return response()->json([
            'data' => new MessageResource($message)
        ], 201);
    }

}
