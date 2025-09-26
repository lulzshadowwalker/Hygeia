<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ChatRoomType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreChatRoomRequest;
use App\Http\Resources\V1\ChatRoomResource;
use App\Models\ChatRoom;
use Illuminate\Http\Request;

class ChatRoomController extends ApiController
{
    public function index()
    {
        $chatRooms = auth()->user()->chatRooms()->with(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])->orderBy('updated_at', 'desc')->get();

        return response()->json([
            'data' => ChatRoomResource::collection($chatRooms),
        ]);
    }

    public function show(ChatRoom $chatRoom)
    {
        $this->authorize('view', $chatRoom);

        $chatRoom->load(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }]);

        return ChatRoomResource::make($chatRoom);
    }

    public function store(StoreChatRoomRequest $request)
    {
        $user = $request->user();

        $participants = $request->participants();

        // Add the creator participant to the participants
        if (! in_array($user->id, $request->participants())) {
            $participants[] = $user->id;
        }

        $chatRoom = ChatRoom::create([
            'type' => ChatRoomType::Standard,
        ]);

        // TODO: This should be moved into an action class with unit testing
        $chatRoom->participants()->attach($participants);
        $chatRoom->load('participants');

        return ChatRoomResource::make($chatRoom);
    }

    public function support()
    {
        $chatRoom = auth()->user()->chatRooms()
            ->where('type', ChatRoomType::Support)
            ->with(['participants', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->firstOr(function () {
                $chatRoom = ChatRoom::create(['type' => ChatRoomType::Support]);
                $chatRoom->addParticipant(auth()->user());

                return $chatRoom;
            });

        $chatRoom->load('participants', 'messages');

        return ChatRoomResource::make($chatRoom)->response()->setStatusCode(200);
    }

    public function join(Request $request, ChatRoom $chatRoom)
    {
        $this->authorize('join', $chatRoom);

        if ($chatRoom->participants->contains(auth()->user())) {
            return $this->response->message('User is already a participant')->build(409);
        }

        $chatRoom->participants()->attach(auth()->user()->id);
        $chatRoom->load('participants');

        return ChatRoomResource::make($chatRoom)
            ->response()
            ->setStatusCode(200);
    }

    public function leave(ChatRoom $chatRoom)
    {
        $this->authorize('leave', $chatRoom);

        if (! $chatRoom->participants->contains(auth()->user())) {
            return response()->json(['message' => 'Successfully left chat room'], 200);
        }

        $chatRoom->participants()->detach(auth()->user()->id);

        return response()->noContent(204);
    }
}
